<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Contracts\View\View;
use MyFatoorah\Library\MyFatoorah;
use MyFatoorah\Library\API\Payment\MyFatoorahPayment;
use MyFatoorah\Library\API\Payment\MyFatoorahPaymentEmbedded;
use MyFatoorah\Library\API\Payment\MyFatoorahPaymentStatus;
// use Exception;
use Auth;
use App\Currency;
use Mockery\Exception;
use Illuminate\Support\Facades\Log;
use Modules\Booking\Models\Booking;
use Modules\User\Models\Wallet\DepositPayment;
use Modules\User\Models\Wallet\Transaction;
use App\Models\User;

class MyFatoorahController extends Controller
{

    /**
     * @var array
     */
    public $mfConfig = [];
    private $apiURL;
    private $apiKey;
    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Initiate MyFatoorah Configuration
     */
    public function __construct()
    {
        $this->apiURL = 'https://apitest.myfatoorah.com';
        $this->apiKey = config('myfatoorah.api_key');
        $this->mfConfig = [
            'apiKey' => config('myfatoorah.api_key'),
            'isTest' => config('myfatoorah.test_mode'),
            'countryCode' => config('myfatoorah.country_iso'),
        ];
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Redirect to MyFatoorah Invoice URL
     * Provide the index method with the order id and (payment method id or session id)
     *
     * @return Response
     */
    public function index()
    {
        try {
            //For example: pmid=0 for MyFatoorah invoice or pmid=1 for Knet in test mode
            $paymentId = request('pmid') ?: 0;
            $sessionId = request('sid') ?: null;

            $orderId = request('oid') ?: 147;
            $curlData = $this->getPayLoadData($orderId);

            $mfObj = new MyFatoorahPayment($this->mfConfig);
            $payment = $mfObj->getInvoiceURL($curlData, $paymentId, $orderId, $sessionId);

            return redirect($payment['invoiceURL']);
        } catch (Exception $ex) {
            $exMessage = __('myfatoorah.' . $ex->getMessage());
            return response()->json(['IsSuccess' => 'false', 'Message' => $exMessage]);
        }
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Example on how to map order data to MyFatoorah
     * You can get the data using the order object in your system
     * 
     * @param int|string $orderId
     * 
     * @return array
     */
    private function getPayLoadData($orderId = null)
    {
        $callbackURL = route('myfatoorah.callback');

        //You can get the data using the order object in your system
        $order = $this->getTestOrderData($orderId);

        return [
            'CustomerName' => 'FName LName',
            'InvoiceValue' => $order['total'],
            'DisplayCurrencyIso' => $order['currency'],
            'CustomerEmail' => 'test@test.com',
            'CallBackUrl' => $callbackURL,
            'ErrorUrl' => $callbackURL,
            'MobileCountryCode' => '+965',
            'CustomerMobile' => '12345678',
            'Language' => 'en',
            'CustomerReference' => $orderId,
            'SourceInfo' => 'Laravel ' . app()::VERSION . ' - MyFatoorah Package ' . MYFATOORAH_LARAVEL_PACKAGE_VERSION
        ];
    }

    private function getRefundData($invoiceId)
    {  
        // $keyId = $invoiceId;   //add this in live mode
        $keyId = "613842"; // invoiceId should be returned in the send /execute payment endpoit response
        $KeyType = 'invoiceId';
        return [
            // Fill required Data
            "KeyType" => $KeyType,
            "Key" => $keyId
        ];
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
    public function success()
    {
        return view('payment.success');
    }
    /**
     * Get MyFatoorah Payment Information
     * Provide the callback method with the paymentId
     * 
     * @return Response
     */
    public function callback(Request $request)
    {
        try {

            $paymentId = request('paymentId');

            $mfObj = new MyFatoorahPaymentStatus($this->mfConfig);
            $data = $mfObj->getPaymentStatus($paymentId, 'PaymentId');

            $message = $this->getTestMessage($data->InvoiceStatus, $data->InvoiceError);
            $response = ['IsSuccess' => true, 'Message' => $message, 'Data' => $data];
            if ($response['IsSuccess']) {
                return $this->changeTransactionStatus($data);
                
                // dd($data->InvoiceStatus);
                // return redirect()->route('myfatoorah.success');
            }
        } catch (Exception $ex) {
            $exMessage = __('myfatoorah.' . $ex->getMessage());
            $response = ['IsSuccess' => 'false', 'Message' => $exMessage];
        }
        return response()->json($response);
    }

    public function walletCallback()
    {
        try {

            $paymentId = request('paymentId');

            $mfObj = new MyFatoorahPaymentStatus($this->mfConfig);
            $data = $mfObj->getPaymentStatus($paymentId, 'PaymentId');

            $message = $this->getTestMessage($data->InvoiceStatus, $data->InvoiceError);
            $response = ['IsSuccess' => true, 'Message' => $message, 'Data' => $data];
            if ($response['IsSuccess']) {
                $this->changeWalletTransactionStatus($data);
                // return redirect()->route('myfatoorah.success');
            }
        } catch (Exception $ex) {
            $exMessage = __('myfatoorah.' . $ex->getMessage());
            $response = ['IsSuccess' => 'false', 'Message' => $exMessage];
        }
        return response()->json($response);
    }

    public function makeRefund($invoiceId)
    {
        $postFields = $this->getRefundData($invoiceId);
        //Call GetRefundStatus endpoint
        $json = $this->callAPI($this->apiURL . "/v2/GetRefundStatus", $this->apiKey, $postFields);


        return $json->Data->RefundStatusResult;
    }

    private function callAPI($endpointURL, $apiKey, $postFields = []) {

        $curl = curl_init($endpointURL);
        curl_setopt_array($curl, array(
            CURLOPT_CUSTOMREQUEST  => 'POST',
            CURLOPT_POSTFIELDS     => json_encode($postFields),
            CURLOPT_HTTPHEADER     => array("Authorization: Bearer $apiKey", 'Content-Type: application/json'),
            CURLOPT_RETURNTRANSFER => true,
        ));
    
        $response = curl_exec($curl);
        $curlErr  = curl_error($curl);
    
        curl_close($curl);
    
        if ($curlErr) {
            //Curl is not working in your server
            die("Curl Error: $curlErr");
        }
    
        $error = $this->handleError($response);
        if ($error) {
            die("Error: $error");
        }
    
        return json_decode($response);
    }

    private function handleError($response) {

        $json = json_decode($response);
        if (isset($json->IsSuccess) && $json->IsSuccess == true) {
            return null;
        }
    
        //Check for the errors
        if (isset($json->ValidationErrors) || isset($json->FieldsErrors)) {
            $errorsObj = isset($json->ValidationErrors) ? $json->ValidationErrors : $json->FieldsErrors;
            $blogDatas = array_column($errorsObj, 'Error', 'Name');
    
            $error = implode(', ', array_map(function ($k, $v) {
                        return "$k: $v";
                    }, array_keys($blogDatas), array_values($blogDatas)));
        } else if (isset($json->Data->ErrorMessage)) {
            $error = $json->Data->ErrorMessage;
        }
    
        if (empty($error)) {
            $error = (isset($json->Message)) ? $json->Message : (!empty($response) ? $response : 'API key or API URL is not correct');
        }
    
        return $error;
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Example on how to Display the enabled gateways at your MyFatoorah account to be displayed on the checkout page
     * Provide the checkout method with the order id to display its total amount and currency
     * 
     * @return View
     */
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

    //-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Example on how the webhook is working when MyFatoorah try to notify your system about any transaction status update
     */
    public function webhook(Request $request)
    {
        try {
            //Validate webhook_secret_key
            $secretKey = config('myfatoorah.webhook_secret_key');
            if (empty($secretKey)) {
                return response(null, 404);
            }

            //Validate MyFatoorah-Signature
            $mfSignature = $request->header('MyFatoorah-Signature');
            if (empty($mfSignature)) {
                return response(null, 404);
            }

            //Validate input
            $body = $request->getContent();
            $input = json_decode($body, true);
            if (empty($input['Data']) || empty($input['EventType']) || $input['EventType'] != 1) {
                return response(null, 404);
            }

            //Validate Signature
            if (!MyFatoorah::isSignatureValid($input['Data'], $secretKey, $mfSignature, $input['EventType'])) {
                return response(null, 404);
            }

            //Update Transaction status on your system
            $result = $this->changeTransactionStatus($input['Data']);

            return response()->json($result);
        } catch (Exception $ex) {
            $exMessage = __('myfatoorah.' . $ex->getMessage());
            return response()->json(['IsSuccess' => false, 'Message' => $exMessage]);
        }
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
    private function changeTransactionStatus($inputData)
    {
        //1. Check if orderId is valid on your system.
        $bookingCode = $inputData->CustomerReference;

        //2. Get MyFatoorah invoice id
        $invoiceId = $inputData->InvoiceId;

        //3. Check order status at MyFatoorah side
        $booking = Booking::where('code', $bookingCode)->first();
        if (!empty($booking) and in_array($booking->status, [$booking::UNPAID])) {
            $message = $this->getTestMessage($inputData->InvoiceStatus, $inputData->InvoiceError);
            $response = ['IsSuccess' => true, 'Message' => 'xyz', 'Data' => $inputData];

            if ($inputData->InvoiceTransactions[0]->TransactionStatus == 'Succss') {

                $payment = $booking->payment;
                if ($payment) {
                    $payment->status = 'completed';
                    $payment->logs = \GuzzleHttp\json_encode($response);
                    $payment->meta = ['Data' => $inputData];
                    $payment->save();
                }
                try {
                    $booking->paid += (float) $booking->pay_now;
                    $booking->markAsPaid();

                } catch (\Exception $e) {
                    \Log::warning($e->getMessage());
                }
                // dd($payment);
                return redirect($booking->getDetailUrl())->with("success", __("You payment has been processed successfully"));
            } else {
                $payment = $booking->payment;
                if ($payment) {
                    $payment->status = 'fail';
                    $payment->logs = \GuzzleHttp\json_encode($response);
                    $payment->save();
                }
                try {
                    $booking->markAsPaymentFailed();

                } catch (\Exception $e) {
                    \Log::warning($e->getMessage());
                }
                return redirect($booking->getDetailUrl())->with("error", __("Payment Failed"));
            }
        } else {
            $mfObj = new MyFatoorahPaymentStatus($this->mfConfig);
            $data = $mfObj->getPaymentStatus($invoiceId, 'InvoiceId');

            $status = $data->InvoiceStatus;
            $error = $data->InvoiceError;
        }

        $message = $this->getTestMessage($status, $error);
        return redirect($booking->getDetailUrl());
        // return $booking->getDetailUrl();
        //4. Update order transaction status on your system
        // return ['IsSuccess' => true, 'Message' => $message, 'Data' => $inputData];
    }

    private function changeWalletTransactionStatus($inputData)
    {
        $payment = DepositPayment::where('code', $inputData->CustomerReference)->first();
        // if($payment->payment_gateway == 'offline_payment' and $payment->status == 'processing'){
        $payment->markAsCompleted();
        //$payment->sendUpdatedPurchaseEmail();
        // }
        $transaction = Transaction::where('id', $payment->wallet_transaction_id)->first();
        $row = User::find($transaction->author_id);
        // event(new UpdateCreditPurchase(Auth::user(), $payment));
        try {
            $row->deposit($payment->amount, [
                'admin_deposit' => auth()->id()
            ]);
        } catch (\Exception $exception) {

            return redirect()->back()->with("error", $exception->getMessage());
        }

        return redirect()->back()->with('success', __('Updated successfully!'));
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
    private function getTestOrderData($orderId)
    {
        return [
            'total' => 15,
            'currency' => 'KWD'
        ];
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
    private function getTestMessage($status, $error)
    {
        if ($status == 'Paid') {
            return 'Invoice is paid.';
        } else if ($status == 'Failed') {
            return 'Invoice is not paid due to ' . $error;
        } else if ($status == 'Expired') {
            return $error;
        }
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------
}
