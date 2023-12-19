<?php

namespace Modules\Booking\Gateways;

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
    public $id   = 'myfatoorah';
    public    $name = 'Myfatoorah Checkout';
    protected $gateway;
    public $mfConfig = [];

    public function __construct() {
        $this->mfConfig = [
            'apiKey'      => config('myfatoorah.api_key'),
            'isTest'      => config('myfatoorah.test_mode'),
            'countryCode' => config('myfatoorah.country_iso'),
        ];
        $this->gateway = new MyFatoorahPayment($this->mfConfig);
    }

    public function getOptionsConfigs()
    {
        return [
            [
                'type'  => 'checkbox',
                'id'    => 'enable',
                'label' => __('Enable Myfatoorah Checkout?'),

            ],
            [
                'type'       => 'input',
                'id'         => 'name',
                'label'      => __('Custom Name'),
                'std'        => __("Myfatoorah Checkout"),
                'multi_lang' => "1"
            ],
            [
                'type'  => 'upload',
                'id'    => 'logo_id',
                'label' => __('Custom Logo'),
            ],
            [
                'type'       => 'editor',
                'id'         => 'html',
                'label'      => __('Custom HTML Description'),
                'multi_lang' => "1"
            ],
            [
                'type'  => 'input',
                'id'    => 'instance_name',
                'label' => __('Instance name'),
            ],
            [
                'type'  => 'input',
                'id'    => 'api_token',
                'label' => __('Api Token'),
                'desc'=>__('Url callback: ')."<b>".route('gateway.webhook',['gateway'=>$this->id])."</b>",
            ]
        ];
    }
 
    public function process(Request $request, $booking, $service = '')
    {
        if (in_array($booking->status, [
            $booking::PAID,
            $booking::COMPLETED,
            $booking::CANCELLED
        ])) {

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
            
            $orderId  = request('oid') ?: $booking['id'];
            $curlData = $this->getPayLoadData($booking);
            
            $mfObj   = new MyFatoorahPayment($this->mfConfig);
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


    private function getPayLoadData($booking) {
        $callbackURL = route('myfatoorah.callback');
        
        //You can get the data using the order object in your system
        // $order = $this->getTestOrderData($orderId);

        return [
            'CustomerName'       => $booking['first_name'],
            'InvoiceValue'       => $booking['total'],
            'DisplayCurrencyIso' => 'USD',
            'CustomerEmail'      => $booking['email'],
            'CallBackUrl'        => $callbackURL,
            'ErrorUrl'           => $callbackURL,
            'MobileCountryCode'  => '+965',
            'CustomerMobile'     => $booking['phone'],
            'Language'           => 'en',
            'CustomerReference'  => $booking['code'],
            'SourceInfo'         => 'Laravel ' . app()::VERSION . ' - MyFatoorah Package ' . MYFATOORAH_LARAVEL_PACKAGE_VERSION
        ];
    }
    private function getTestOrderData($orderId) {
        return [
            'total'    => 15,
            'currency' => 'KWD'
        ];
    }

    public function checkout() {
        try {
            //You can get the data using the order object in your system
            $orderId = request('oid') ?: 147;
            $order   = $this->getTestOrderData($orderId);

            //You can replace this variable with customer Id in your system
            $customerId = request('customerId');

            //You can use the user defined field if you want to save card
            $userDefinedField = config('myfatoorah.save_card') && $customerId ? "CK-$customerId" : '';

            //Get the enabled gateways at your MyFatoorah acount to be displayed on checkout page
            $mfObj          = new MyFatoorahPaymentEmbedded($this->mfConfig);
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
            $jsDomain  = ($isTest) ? $countries[$vcCode]['testPortal'] : $countries[$vcCode]['portal'];

            return view('myfatoorah.checkout', compact('mfSession', 'paymentMethods', 'jsDomain', 'userDefinedField'));
        } catch (Exception $ex) {
            $exMessage = __('myfatoorah.' . $ex->getMessage());
            return view('myfatoorah.error', compact('exMessage'));
        }
    }

    // public function handlePurchaseData($data, $booking, $request)
    // {
    //     $myfatoorah_args = array();
    //     $myfatoorah_args['sid'] = $this->getOption('myfatoorah_account_number');
    //     $myfatoorah_args['paypal_direct'] = 'Y';
    //     $myfatoorah_args['cart_order_id'] = $booking->code;
    //     $myfatoorah_args['merchant_order_id'] = $booking->code;
    //     $myfatoorah_args['total'] = (float) $booking->pay_now;
    //     $myfatoorah_args['return_url'] = $this->getCancelUrl().'?c='.$booking->code;
    //     $myfatoorah_args['x_receipt_link_url'] = $this->getReturnUrl().'?c='.$booking->code;
    //     $myfatoorah_args['currency_code'] = setting_item('currency_main');
    //     $myfatoorah_args['card_holder_name'] = $request->input("first_name").' '.$request->input("last_name");
    //     $myfatoorah_args['street_address'] = $request->input("address_line_1");
    //     $myfatoorah_args['street_address2'] = $request->input("address_line_1");
    //     $myfatoorah_args['city'] = $request->input("city");
    //     $myfatoorah_args['state'] = $request->input("state");
    //     $myfatoorah_args['country'] = $request->input("country");
    //     $myfatoorah_args['zip'] = $request->input("zip_code");
    //     $myfatoorah_args['phone'] = "";
    //     $myfatoorah_args['email'] = $request->input("email");
    //     $myfatoorah_args['lang'] = app()->getLocale();
    //     return $myfatoorah_args;
    // }

    // public function getDisplayHtml()
    // {
    //     $location = app()->getLocale();
    //     if (setting_item('site_locale') == $location){
    //         return $this->getOption('html', '');
    //     } else {
    //         return $this->getOption('html_'.$location);
    //     }
    // }

    // public function confirmPayment(Request $request)
    // {
    //     $c = $request->query('c');
    //     $booking = Booking::where('code', $c)->first();
    //     if (!empty($booking) and  !in_array($booking->payment_status, [
    //             $booking::PAID,
    //             $booking::COMPLETED,
    //             $booking::CANCELLED])) {
    //         $checkPayment = $this->checkPayment($booking);
    //         $status  = $checkPayment->getStatus();
    //         if ($status != 'confirmed') {
    //             $payment = $booking->payment;
    //             if ($payment) {
    //                 $data = $checkPayment->toArray($checkPayment);
    //                 $payment->status = 'fail';
    //                 $payment->logs = \GuzzleHttp\json_encode($data);
    //                 $payment->save();
    //             }
    //             try {
    //                 if($status =='waiting'){
    //                         $booking->markAsProcessing($booking,[]);
    //                     return redirect($booking->getDetailUrl())->with("error", __("Your payment has been placed"));
    //                 }else{
    //                     $booking->markAsPaymentFailed();
    //                 }
    //             } catch (\Swift_TransportException $e) {
    //                 Log::warning($e->getMessage());
    //             }
    //             return redirect($booking->getDetailUrl())->with("error", __("Payment Failed"));
    //         } else {
    //             $payment = $booking->payment;
    //             if ($payment) {
    //                 $data = $checkPayment->toArray($checkPayment);
    //                 $payment->status = 'completed';
    //                 $payment->logs = \GuzzleHttp\json_encode($data);
    //                 $payment->save();
    //             }
    //             try {
    //                 $booking->paid += (float) $booking->pay_now;
    //                 $booking->markAsPaid(Booking::CONFIRMED);

    //             } catch (\Swift_TransportException $e) {
    //                 Log::warning($e->getMessage());
    //             }
    //             return redirect($booking->getDetailUrl())->with("success", __("You payment has been processed successfully"));
    //         }
    //     }
    //     if (!empty($booking)) {
    //         return redirect($booking->getDetailUrl(false));
    //     } else {
    //         return redirect(url('/'));
    //     }
    // }
    // public function callbackPayment(Request $request)
    // {
    //     $transaction = $request->transaction;
    //     if(!empty($transaction['referenceId'])){
    //         $booking = Booking::where('code', $transaction['referenceId'])->first();
    //         if (!empty($booking) and !in_array($booking->payment_status, [
    //                 $booking::PAID,
    //                 $booking::COMPLETED,
    //                 $booking::CANCELLED])) {

    //             $checkPayment = $this->checkPayment($booking,$transaction);
    //             $status  = $checkPayment->getStatus();
    //             $amount = $checkPayment->getAmount();
    //             if ($status != 'confirmed') {
    //                 $payment = $booking->payment;
    //                 if ($payment) {
    //                     $data = $checkPayment->toArray($checkPayment);
    //                     $payment->status = 'fail';
    //                     $payment->logs = \GuzzleHttp\json_encode($data);
    //                     $payment->save();
    //                 }
    //                 try {
    //                     if($status =='waiting'){
    //                         $booking->markAsProcessing($booking,[]);
    //                         return response()->json(['status'=>'error',"message"=> __("Payment Processing")]);
    //                     }elseif ($status=='authorized'){
    //                             $booking->markAsProcessing($payment, []);
    //                         return response()->json(['status'=>'error',"message"=> __("Payment Processing")]);
    //                     }else {
    //                         $booking->markAsPaymentFailed();
    //                         return response()->json(['status'=>'error',"message"=> __("Payment Failed.")]);
    //                     }
    //                 } catch (\Swift_TransportException $e) {
    //                     return response()->json(['status'=>'error',"message"=> __("Payment Failed")]);
    //                 }
    //             } else {
    //                 $payment = $booking->payment;
    //                 if ($payment) {
    //                     $data = $checkPayment->toArray($checkPayment);
    //                     $payment->status = 'completed';
    //                     $payment->logs = \GuzzleHttp\json_encode($data);
    //                     $payment->save();
    //                 }
    //                 try {
    //                     $booking->paid += (float) ($amount/100);
    //                     $booking->markAsPaid();


    //                 } catch (\Swift_TransportException $e) {
    //                     return response()->json(['status'=>'error',"message"=> $e->getMessage()]);
    //                 }

    //                 return response()->json(['status'=>'success',"message"=> __("You payment has been processed successfully before")]);
    //             }
    //         }
    //         if (!empty($booking)) {
    //             return response()->json(['status'=>'success',"message"=> __("No information found")]);
    //         } else {
    //             return response()->json(['status'=>'error',"message"=> __("No information found")]);
    //         }
    //     }else{
    //         return response()->json(['status'=>'error',"message"=> __("referenceId can't null")]);
    //     }

    // }


    // public function cancelPayment(Request $request)
    // {
    //     $c = $request->query('c');
    //     $booking = Booking::where('code', $c)->first();
    //     if (!empty($booking) and in_array($booking->status, [Booking::DRAFT])) {
    //         $payment = $booking->payment;
    //         if ($payment) {
    //             $payment->status = 'cancel';
    //             $payment->logs = \GuzzleHttp\json_encode([
    //                 'customer_cancel' => 1
    //             ]);
    //             $payment->save();
    //         }
    //         return redirect()->to(route('booking.cancel'))->with("error", __("You cancelled the payment"));
    //     }
    //     return redirect()->to(route('booking.cancel'));
    // }

    // public function checkPayment($booking,$transaction=false){
    //     $myfatoorahId = $booking->getMeta('myfatoorahId');
    //     $instanceName = $this->getOption('instance_name');
    //     $secret = $this->getOption('api_secret_key');
    //     $myfatoorah = new \Myfatoorah\Myfatoorah($instanceName, $secret);
    //     $gateway = new \Myfatoorah\Models\Request\Gateway();


    //     if(!empty($transaction['id'])){
    //         //For webhooks
    //         $transition = new \Myfatoorah\Models\Request\Transaction();
    //         $transition->setId($transaction['id']);
    //         try {
    //             $response = $myfatoorah->getOne($transition);

    //             if(!empty($response->getStatus())){
    //                 return $response;
    //             }
    //         } catch (\Myfatoorah\MyfatoorahException $e) {
    //             print $e->getMessage();
    //         }

    //     }else{
    //         // Khong the capture dc gateway o day,
    //         $gateway->setId($myfatoorahId);
    //         try {
    //             $response = $myfatoorah->getOne($gateway);

    //             if(!empty($response->getStatus())){
    //                 return $response;
    //             }
    //         } catch (\Myfatoorah\MyfatoorahException $e) {
    //             print $e->getMessage();
    //         }
    //     }

    // }
    // public function getDisplayLogo()
    // {
    //     $logo_id = $this->getOption('logo_id');
    //     return get_file_url($logo_id,'medium');
    // }

}
