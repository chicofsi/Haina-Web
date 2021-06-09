<?php

namespace App\Http\Controllers\Api\Midtrans;

use App\Http\Controllers\Controller;
use App\Http\Controllers\NotificationController;
use Illuminate\Http\Request;
use App\Http\Resources\ValueMessage;
use Illuminate\Support\Facades\Validator;

use GuzzleHttp\Client;
use GuzzleHttp\TransferStats;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use App\Models\Transaction;
use App\Models\TransactionInquiry;
use App\Models\TransactionPayment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\NotificationCategory;
use App\Models\HotelBooking;
use App\Models\EspayRequest;
use App\Models\HotelBookingPayment;
use App\Models\PersonalAccessToken;

use App\Models\User;
use DateTime;


class MidtransController extends Controller
{
    public function __construct()
    {
        $header="Basic ".base64_encode("hainaapp".":"."zclwXJlnApNbBhYF");
        $this->client = new Client([
            'base_uri' => 'https://sandbox-api.espay.id/rest/biller/',
            'timeout'  => 150.0,
            'headers' => [
                'Authorization' => $header,
            ]
        ]);
    }
	public function notificationHandler (Request $request)
    {
        $order_id=$request->order_id;
        $transaction_id=$request->transaction_id;
        $method=$request->payment_type;
        $transaction_time=$request->transaction_time;
        $transaction_status=$request->transaction_status;
        $status_code=$request->status_code;
        $gross_amount=$request->gross_amount;

        if($request->custom_field1=="PPOB"){

            $transaction=Transaction::where('order_id',$order_id)->with('product')->first();
            $status="";

            $token = [];
            $usertoken = PersonalAccessToken::select('name')->where('tokenable_id', $transaction['id_user'])->get();

            $product_group = Product::select('id_product_group', 'description')->where('id',$transaction['id_product'])->first();
            $product_category = ProductGroup::select('id_product_category')->where('id', $product_group['id_product_group'])->first();
            $product_type = ProductCategory::where('id', $product_category['id_product_category'])->first();

            $transaction_product = $product_type['name'];
            $transaction_amount = number_format($transaction['total_payment'], 2, ",", ".");

            foreach($usertoken as $key => $value){
                array_push($token, $value); 
            }

            if($transaction_status=='settlement'){
                $settlement_time=date("Y-m-d h:m:s",strtotime($request->settlement_time));
                $status='process';
                NotificationController::sendPush($token, "Payment successful", "Your Rp ".$transaction_amount."payment for ".$transaction_product." is successful", "Transaction");
            }else if($transaction_status=='pending'){
                $settlement_time=null;
                $status='pending payment';
                NotificationController::sendPush($token, "Waiting for payment", "There is a pending payment for ".$transaction_product.". Please finish payment in 24 hours", "Transaction");
            }else if($transaction_status=='expire'){
                $settlement_time=null;
                $status='unsuccess';
            }else if($transaction_status=='cancel'){
                $settlement_time=null;
                $status='unsuccess';
            }

            $transaction=Transaction::where('order_id',$order_id)->update(['status'=>$status]);
            $transaction=Transaction::where('order_id',$order_id)->with('product')->first();
            
            if($transaction_status=='settlement'){
                $this->espayPayment($order_id);
            }

            foreach ($request['va_numbers'] as $key => $value) {
                $va_number=$value['va_number'];
                $payment=PaymentMethod::where('name',$value['bank'])->first();
            }

            $transactionpayment=TransactionPayment::updateOrCreate(
                [
                    'id_transaction' => $transaction->id
                ],
                [
                    'midtrans_id' => $transaction_id,
                    'id_payment_method' => $payment->id,
                    'settlement_time' => $settlement_time,
                    'payment_status' => $transaction_status,
                    'va_number' => $va_number
                ]);

            return $transactionpayment;
        }else if($request->custom_field1=="Hotel"){
            $status="";
            if($transaction_status=='settlement'){
                $settlement_time=date("Y-m-d h:m:s",strtotime($request->settlement_time));
                $status='PAID';
            }else if($transaction_status=='pending'){
                $settlement_time=null;
                $status='UNPAID';
            }else if($transaction_status=='expire'){
                $settlement_time=null;
                $status='CANCELLED';
            }else if($transaction_status=='cancel'){
                $settlement_time=null;
                $status='CANCELLED';
            }

            $hotelbooking=HotelBooking::where('order_id',$order_id)->update(['status'=>$status]);
            $hotelbooking=HotelBooking::where('order_id',$order_id)->with('hotel','room')->first();

            foreach ($request['va_numbers'] as $key => $value) {
                $va_number=$value['va_number'];
                $payment=PaymentMethod::where('name',$value['bank'])->first();
            }

            $hotelbookingpayment=HotelBookingPayment::updateOrCreate(
                [
                    'booking_id' => $hotelbooking->id
                ],
                [
                    'midtrans_id' => $transaction_id,
                    'payment_method_id' => $payment->id,
                    'settlement_time' => $settlement_time,
                    'payment_status' => $transaction_status,
                    'va_number' => $va_number
                ]);
            return $hotelbookingpayment;
        }

    }

    public function espayPayment($order_id)
    {
        $transaction=Transaction::where('order_id',$order_id)->with('product')->first();
        $datetime=Date('Y-m-d H:m:s');
        $time = Date('YmdHms');

        $uuid="HAINAAPP".$order_id."inq".$time;

        //$uuid=$request->uuid;
        $sender_id="HAINAAPP";
        $password="zclwXJlnApNbBhYF";
        $amount=($transaction->total_payment-$transaction->profit)*100;
        $current_date = new DateTime();
        $signature=hash('sha256',strtoupper("##".$sender_id."##".$transaction->customer_number."##".$transaction->product->product_code."##".$amount."##".$uuid."##djHKvcScStINUlaK##"),false);

        $body=[
            "rq_uuid"       => $uuid,
            "rq_datetime"   => $datetime,
            "sender_id"     => $sender_id,
            "password"      => $password,
            "order_id"      => $transaction->customer_number,
            "product_code"  => $transaction->product->product_code,
            "amount"        => $amount,
            "signature"     => $signature
        ];

        $product=Product::where('id',$transaction->product->id)->first();

        if($product->inquiry_type=="inquiry"){
            $body["data"]=json_decode(TransactionInquiry::where('order_id',$order_id)->first()->inquiry_data);
        }
        try {
            $response=$this->client->request(
                'POST',
                'paymentreport',
                [
                    'form_params' => $body,
                    'on_stats' => function (TransferStats $stats) use (&$url) {
                        $url = $stats->getEffectiveUri();
                    }
                ]  
            );

            $bodyresponse=$response->getBody()->getContents();
            EspayRequest::insert(
                [
                    'order_id'=>$transaction->order_id,
                    'uuid'=>$uuid,
                    'request'=>json_encode($body),
                    'response'=>$bodyresponse,
                    'error_code'=>json_decode($bodyresponse)->error_code,   
                    'url'=>$url,
                    'response_code'=>$response->getStatusCode(),
                ]
            );

            $bill = json_decode($bodyresponse);
            
            if(isset($bill) && $bill->error_code == "0000"){
                $transaction=Transaction::where('order_id',$order_id)->update(['status'=>'success']);

                return 1;
            }
            else if($bill->error_code == 610){
                return 0;
            }
            else if($bill->error_code == 802){
                return 0;
            }
            else{
                return 0;
            }

        }catch(RequestException $e) {
            echo Psr7\Message::toString($e->getRequest());
            if ($e->hasResponse()) {
                echo Psr7\Message::toString($e->getResponse());
            }
            return;
        }
    }
}
