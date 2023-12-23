<?php

namespace Modules\Booking\Gateways;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Mockery\Exception;
use Modules\Booking\Models\Booking;
use Modules\Booking\Events\BookingCreatedEvent;
use Modules\Booking\Models\Payment;
use MyFatoorah\Library\MyFatoorah;
use MyFatoorah\Library\API\Payment\MyFatoorahPayment;
use MyFatoorah\Library\API\Payment\MyFatoorahPaymentEmbedded;
use MyFatoorah\Library\API\Payment\MyFatoorahPaymentStatus;
use Modules\Hotel\Models\HotelRoomBooking;
use Carbon\Carbon;

class MyFatoorahGateway extends BaseGateway
{
    public $id = 'myfatoorah';
    public $name = 'Myfatoorah Checkout';
    protected $gateway;
    public $mfConfig = [];

    public function __construct()
    {
        $this->mfConfig = [
            'apiKey' => config('myfatoorah.api_key'),
            'isTest' => config('myfatoorah.test_mode'),
            'countryCode' => config('myfatoorah.country_iso'),
        ];
        $this->gateway = new MyFatoorahPayment($this->mfConfig);
    }

    public function getOptionsConfigs()
    {
        return [
            [
                'type' => 'checkbox',
                'id' => 'enable',
                'label' => __('Enable Myfatoorah Checkout?'),

            ],
            [
                'type' => 'input',
                'id' => 'name',
                'label' => __('Custom Name'),
                'std' => __("Myfatoorah Checkout"),
                'multi_lang' => "1"
            ],
            [
                'type' => 'upload',
                'id' => 'logo_id',
                'label' => __('Custom Logo'),
            ],
            [
                'type' => 'editor',
                'id' => 'html',
                'label' => __('Custom HTML Description'),
                'multi_lang' => "1"
            ],
            [
                'type' => 'input',
                'id' => 'instance_name',
                'label' => __('Instance name'),
            ],
            [
                'type' => 'input',
                'id' => 'api_token',
                'label' => __('Api Token'),
                'desc' => __('Url callback: ') . "<b>" . route('gateway.webhook', ['gateway' => $this->id]) . "</b>",
            ]
        ];
    }

    public function process(Request $request, $booking, $service = '')
    {
        if (
            in_array($booking->status, [
                    $booking::PAID,
                    $booking::COMPLETED,
                    $booking::CANCELLED
            ])
        ) {

            throw new Exception(__("Booking status does need to be paid"));
        }
        if (!$booking->pay_now) {
            throw new Exception(__("Booking total is zero. Can not process payment gateway!"));
        }

        $payment = new Payment();
        $payment->booking_id = $booking->id;
        $payment->payment_gateway = $this->id;
        $payment->status = 'draft';

        try {
            //For example: pmid=0 for MyFatoorah invoice or pmid=1 for Knet in test mode
            $paymentId = request('pmid') ?: 0;
            $sessionId = request('sid') ?: null;

            $orderId = request('oid') ?: $booking['id'];
            $curlData = $this->getPayLoadData($booking);

            $mfObj = new MyFatoorahPayment($this->mfConfig);
            $response = $mfObj->getInvoiceURL($curlData, $paymentId, $orderId, $sessionId);


            if (!empty($response['invoiceURL'])) {
                $payment->save();
                $booking->status = $booking::UNPAID;
                $booking->payment_id = $payment->id;
                
                $booking->save();
                try {
                    event(new BookingCreatedEvent($booking));
                } catch (\Exception $e) {
                    Log::warning($e->getMessage());
                }

                response()->json([
                    'url' => $response['invoiceURL']
                ])->send();

                // return redirect($response['invoiceURL']);
            }

        } catch (Exception $ex) {
            $exMessage = __('myfatoorah.' . $ex->getMessage());
            return response()->json(['IsSuccess' => 'false', 'Message' => $exMessage]);
        }
    }

    public function getAuthorizationResponse($data)
    {
        try {
            //For example: pmid=0 for MyFatoorah invoice or pmid=1 for Knet in test mode
            $paymentId = request('pmid') ?: 0;
            $sessionId = request('sid') ?: null;

            $bookingId = request('oid') ?: $data['CustomerReference'];
            // $curlData = $this->getPayLoadData($data);
            $mfObj = new MyFatoorahPayment($this->mfConfig);
            $response = $mfObj->getInvoiceURL($data, $paymentId, $bookingId, $sessionId);

            return $response;
        } catch (Exception $ex) {
            $exMessage = __('myfatoorah.' . $ex->getMessage());
            return response()->json(['IsSuccess' => 'false', 'Message' => $exMessage]);
        }
    }
    public function processNormal($payment)
    {
        $this->getGateway();
        $payment->payment_gateway = $this->id;
        $data = $this->getPayLoadData($payment);
        $user = User::find(6);
        $data['CustomerName'] = $user->first_name;
        $data['InvoiceValue'] = $payment->amount;
        $data['CustomerEmail'] = $payment->email;
        $data['CustomerMobile'] = $user->phone;
        $data['CallBackUrl'] = route('myfatoorah.walletCallback');
        $response = $this->getAuthorizationResponse($data);
        if (!empty($response['invoiceURL'])) {
            return [true, false, $response['invoiceURL']];
        } else {
            return [false, $response->getMessage()];
        }
    }

    public function getGateway()
    {
        return $this->gateway;
    }

    private function getPayLoadData($booking)
    {
        $callbackURL = route('myfatoorah.callback');

        //You can get the data using the order object in your system
        // $order = $this->getTestOrderData($orderId);

        return [
            'CustomerName' => $booking['first_name'],
            'InvoiceValue' => $booking['total'],
            'DisplayCurrencyIso' => 'USD',
            'CustomerEmail' => $booking['email'],
            'CallBackUrl' => $callbackURL,
            'ErrorUrl' => $callbackURL,
            'MobileCountryCode' => '+965',
            'CustomerMobile' => $booking['phone'],
            'Language' => 'en',
            'CustomerReference' => $booking['code'],
            'SourceInfo' => 'Laravel ' . app()::VERSION . ' - MyFatoorah Package ' . MYFATOORAH_LARAVEL_PACKAGE_VERSION
        ];
    }
    private function getTestOrderData($orderId)
    {
        return [
            'total' => 15,
            'currency' => 'KWD'
        ];
    }

    public function checkout()
    {
        try {
            //You can get the data using the order object in your system
            $orderId = request('oid') ?: 147;
            $order = $this->getTestOrderData($orderId);

            //You can replace this variable with customer Id in your system
            $customerId = request('customerId');

            //You can use the user defined field if you want to save card
            $userDefinedField = config('myfatoorah.save_card') && $customerId ? "CK-$customerId" : '';

            //Get the enabled gateways at your MyFatoorah acount to be displayed on checkout page
            $mfObj = new MyFatoorahPaymentEmbedded($this->mfConfig);
            $paymentMethods = $mfObj->getCheckoutGateways($order['total'], $order['currency'], config('myfatoorah.register_apple_pay'));

            if (empty($paymentMethods['all'])) {
                throw new Exception('noPaymentGateways');
            }

            //Generate MyFatoorah session for embedded payment
            $mfSession = $mfObj->getEmbeddedSession($userDefinedField);

            //Get Environment url
            $isTest = $this->mfConfig['isTest'];
            $vcCode = $this->mfConfig['countryCode'];

            $countries = MyFatoorah::getMFCountries();
            $jsDomain = ($isTest) ? $countries[$vcCode]['testPortal'] : $countries[$vcCode]['portal'];

            return view('myfatoorah.checkout', compact('mfSession', 'paymentMethods', 'jsDomain', 'userDefinedField'));
        } catch (Exception $ex) {
            $exMessage = __('myfatoorah.' . $ex->getMessage());
            return view('myfatoorah.error', compact('exMessage'));
        }
    }

}
