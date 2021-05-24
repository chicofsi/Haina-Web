<?php

namespace App\Http\Controllers\Api\Midtrans;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ValueMessage;
use Illuminate\Support\Facades\Validator;

use App\Models\Transaction;
use App\Models\TransactionPayment;
use App\Models\PaymentMethod;
use App\Models\NotificationCategory;

class MidtransController extends Controller
{
	public function notificationHandler (Request $request)
    {
        $order_id=$request->order_id;
        $transaction_id=$request->transaction_id;
        $method=$request->payment_type;
        $transaction_time=$request->transaction_time;
        $transaction_status=$request->transaction_status;
        $status="";
        if($transaction_status=='settlement'){
            $settlement_time=date("Y-m-d h:m:s",strtotime($request->settlement_time));
            $status='process';
        }else if($transaction_status=='pending'){
            $settlement_time=null;
            $status='pending payment';
        }else if($transaction_status=='expire'){
            $settlement_time=null;
            $status='unsuccess';
        }else if($transaction_status=='cancel'){
            $settlement_time=null;
            $status='unsuccess';
        }
        $status_code=$request->status_code;
        $gross_amount=$request->gross_amount;

        $transaction=Transaction::where('order_id',$order_id)->update(['status'=>$status]);
        $transaction=Transaction::where('order_id',$order_id)->with('product')->first();

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
        return $this->espayPayment($transaction->order_id, date("Y-m-d h:m:s"), $transaction->product->product_code, $transaction->product->base_price, $transaction->customer_number);
    }

    public function espayInquiry($order_id, $datetime, $product_code, $number, $data = false)
    {
        $uuid="HAINAAPP".$order_id."inq";
        $sender_id="HAINAAPP";
        $password="zclwXJlnApNbBhYF";
        $signature=base64_encode(strtoupper("##".$sender_id."##".$number."##".$product_code."##".$uuid."##djHKvcScStINUlaK##"));

        $body=array(
            "rq_uuid"          => $uuid,
            "rq_datetime"  => $datetime,
            "sender_id"  => $sender_id,
            "password"  => $password,
            "order_id"  => $order_id,
            "product_code"  => $product_code,
            "signature"  => $signature,
        );

        if($data){
            $body["additional_data"]=$data;
        }

        $header="Authorization: Basic ".base64_encode("hainaapp".":"."zclwXJlnApNbBhYF");

        $call=$this->callAPI('https://sandbox-api.espay.id/rest/biller/inquirytransaction', $body, $header);
    }
    public function espayPayment($order_id, $datetime, $product_code, $amount, $number, $data = false)
    {
        $uuid="HAINAAPP".$order_id."pay";
        $sender_id="HAINAAPP";
        $password="zclwXJlnApNbBhYF";
        $signature=base64_encode(strtoupper("##".$sender_id."##".$number."##".$product_code."##".$amount."##".$uuid."##djHKvcScStINUlaK##"));

        $body=array(
            "rq_uuid"      => $uuid,
            "rq_datetime"  => $datetime,
            "sender_id"  => $sender_id,
            "password"  => $password,
            "order_id"  => $order_id,
            "product_code"  => $product_code,
            "amount"  => $amount,
            "signature"  => $signature,
        );

        if($data){
            $body["additional_data"]=$data;
        }

        $header="Authorization: Basic ".base64_encode("hainaapp".":"."zclwXJlnApNbBhYF");

        return $this->callAPI('https://sandbox-api.espay.id/rest/biller/paymentreport', $body, $header);
    }

    function callAPI( $url, $data, $header = false){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_POST, 1);
        if ($data)
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

        // OPTIONS:
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        if(!$header){
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
            ));
        }else{
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                $header
            ));
        }
        // EXECUTE:
        $result = curl_exec($curl);
        if(!$result){die("Connection Failure");}
        curl_close($curl);
        return $result;
    }

    


}
