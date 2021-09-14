<?php

namespace App\Http\Controllers\Api\Midtrans;

use App\Http\Controllers\Controller;

use App\Http\Controllers\Api\Hotel\HotelDarmaController;
use App\Http\Controllers\Api\Notification\NotificationController;
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
use App\Models\ProductGroup;
use App\Models\ProductCategory;
use App\Models\NotificationCategory;
use App\Models\HotelBooking;
use App\Models\EspayRequest;
use App\Models\HotelBookingPayment;
use App\Models\PersonalAccessToken;
use App\Models\HotelDarmaPayment;
use App\Models\HotelDarmaBooking;
use App\Models\HotelDarma;
use App\Models\JobVacancy;
use App\Models\JobVacancyPayment;
use App\Models\Company;
use App\Models\FlightBooking;
use App\Models\FlightBookingDetails;
use App\Models\FlightBookingPayment;
use App\Models\AdminAlert;

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
        $this->clientbalance = new Client([
            'base_uri' => 'https://sandbox-api.espay.id/rest/billertools/',
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
                array_push($token, $value->name); 
            }

            if($transaction_status=='settlement'){
                $settlement_time=date("Y-m-d H:i:s",strtotime($request->settlement_time));
                $status='process';
                foreach ($token as $key => $value) {
                    NotificationController::sendPush($transaction['id_user'],$value, "Payment successful", "Your Rp ".$transaction_amount." payment for ".$transaction_product." is successful", "Transaction","finish");
                }
            }else if($transaction_status=='pending'){
                $settlement_time=null;
                $status='pending payment';
                foreach ($token as $key => $value) {
                    NotificationController::sendPush($transaction['id_user'],$value, "Waiting for payment", "There is a pending payment for ".$transaction_product.". Please finish payment in 24 hours", "Transaction","unfinish");
                }
            }else if($transaction_status=='expire'){
                $settlement_time=null;
                $status='unsuccess';
            }else if($transaction_status=='cancel'){
                $settlement_time=null;
                $status='unsuccess';
                foreach ($token as $key => $value) {
                    NotificationController::sendPush($transaction['id_user'],$value, "Transaction cancelled", "Your transaction for ".$transaction_product." has been successfully cancelled.", "Transaction","cancel");
                }
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

            $transaction = HotelBooking::where('order_id',$order_id)->with('hotel','room')->first();

            $token = [];
            $usertoken = PersonalAccessToken::select('name')->where('tokenable_id', $transaction['id_user'])->get();

            $hotel_name = Hotel::select('hotel_name')->where('id', $transaction->hotel->id)->first();
            $hotel_amount = number_format($transaction['total_price'], 2, ",", ".");

            foreach($usertoken as $key => $value){
                array_push($token, $value->name); 
            }

            $status="";
            if($transaction_status=='settlement'){
                $settlement_time=date("Y-m-d H:i:s",strtotime($request->settlement_time));
                $status='PAID';
                foreach ($token as $key => $value) {

                    NotificationController::sendPush($transaction['id_user'],$value, "Payment successful", "Your Rp ".$hotel_amount."payment for booking at".$hotel_name." is successful", "Hotel", "finish");
                }
            }else if($transaction_status=='pending'){
                $settlement_time=null;
                $status='UNPAID';
                foreach ($token as $key => $value) {

                    NotificationController::sendPush($transaction['id_user'],$value, "Waiting for payment", "There is a pending payment for booking at ".$hotel_name.". Please finish payment in 24 hours", "Hotel", "unfinish");
                }
            }else if($transaction_status=='expire'){
                $settlement_time=null;
                $status='CANCELLED';
            }else if($transaction_status=='cancel'){
                $settlement_time=null;
                $status='CANCELLED';
                foreach ($token as $key => $value) {

                    NotificationController::sendPush($transaction['id_user'],$value, "Booking cancelled", "Your booking for ".$hotel_name." has been cancelled.", "Hotel", "cancel");
                }
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
        else if($request->custom_field1=="HotelDarma"){
            $transaction = HotelDarmaBooking::where('agent_os_ref',$order_id)->with('hotel','room')->first();

            $token = [];
            $usertoken = PersonalAccessToken::select('name')->where('tokenable_id', $transaction['user_id'])->get();

            $hotel_name = HotelDarma::select('hotel_name')->where('id', $transaction->hotel_id)->first();
            $hotel_amount = number_format($transaction['total_price'], 2, ",", ".");

            foreach($usertoken as $key => $value){
                array_push($token, $value->name); 
            }

            $status="";
            if($transaction_status=='settlement'){
                $settlement_time=date("Y-m-d H:i:s",strtotime($request->settlement_time));
                $status='process';
                foreach ($token as $key => $value) {

                    NotificationController::sendPush($transaction['user_id'],$value, "Payment successful ", "Your Rp ".$hotel_amount." payment for booking at".$hotel_name['hotel_name']." is successful", "Hotel", "finish");
                }

                $book = new HotelDarmaController();
                $book->issueBooking($transaction['user_id']);
                //HotelDarmaController::issueBooking();

            }else if($transaction_status=='pending'){
                $settlement_time=null;
                $status='pending';
                foreach ($token as $key => $value) {
                    
                    NotificationController::sendPush($transaction['user_id'],$value, "Waiting for payment", "There is a pending payment for booking at ".$hotel_name['hotel_name'].". Please finish payment soon.", "Hotel", "unfinish");
                }
            }else if($transaction_status=='expire'){
                $settlement_time=null;
                $status='expire';
            }else if($transaction_status=='cancel'){
                $settlement_time=null;
                $status='cancel';
                NotificationController::sendPush($transaction['id_user'],$valuw, "Booking cancelled", "Your booking for ".$hotel_name['hotel_name']." has been cancelled.", "Hotel", "cancel");
            }

            $hotelbooking=HotelDarmaBooking::where('agent_os_ref',$order_id)->update(['status'=>$status]);
            $hotelbookingdata=HotelDarmaBooking::where('agent_os_ref',$order_id)->with('hotel','room')->first();

            foreach ($request['va_numbers'] as $key => $value) {
                $va_number=$value['va_number'];
                $payment=PaymentMethod::where('name',$value['bank'])->first();
            }

            $hotelbookingpayment=HotelDarmaPayment::updateOrCreate(
                [
                    'booking_id' => $hotelbookingdata->id
                ],
                [
                    'midtrans_id' => $transaction_id,
                    'payment_method_id' => $payment->id,
                    'settlement_time' => $settlement_time,
                    'payment_status' => $transaction_status,
                    'va_number' => $va_number
                ]);
            return $hotelbookingpayment;

        }else if($request->custom_field1=="Flight"){

            $transaction = FlightBooking::where('order_id',$order_id)->with('flightbookingdetails')->first();

            $token = [];
            $usertoken = PersonalAccessToken::select('name')->where('tokenable_id', $transaction['id_user'])->get();

            $flight_details = FlightBookingDetails::where('id_flight_book', $transaction->id)->with('depart','arrival')->first();
            $transaction_amount = number_format($transaction['amount'], 2, ",", ".");

            foreach($usertoken as $key => $value){
                array_push($token, $value->name); 
            }

            $status="";
            if($transaction_status=='settlement'){
                $settlement_time=date("Y-m-d H:i:s",strtotime($request->settlement_time));
                $status='process';
                foreach ($token as $key => $value) {

                    NotificationController::sendPush($transaction['id_user'],$value, "Payment successful", "Your Rp ".$transaction_amount." payment for flight ticket from ".$flight_details->depart_from." to ".$flight_details->depart_to." is successful", "Flight","finish");
                }
            }else if($transaction_status=='pending'){
                $settlement_time=null;
                $status='pending';
                foreach ($token as $key => $value) {

                    NotificationController::sendPush($transaction['id_user'],$value, "Waiting for payment", "There is a pending payment for flight ticket from ".$flight_details->depart_from." to ".$flight_details->depart_to.". Please finish payment in 24 hours", "Flight", "unfinish");
                }
            }else if($transaction_status=='expire'){
                $settlement_time=null;
                $status='expired';
            }else if($transaction_status=='cancel'){
                $settlement_time=null;
                $status='canceled';
                foreach ($token as $key => $value) {

                    NotificationController::sendPush($transaction['id_user'],$value, "Booking cancelled", "Your booking for flight ticket from ".$flight_details->depart_from." to ".$flight_details->depart_to." has been cancelled.", "Flight", "unfinish");
                }
            }

            $flightbooking=FlightBooking::where('order_id',$order_id)->update(['status'=>$status]);
            $flightbooking=FlightBooking::where('order_id',$order_id)->with('flightbookingdetails')->first();

            foreach ($request['va_numbers'] as $key => $value) {
                $va_number=$value['va_number'];
                $payment=PaymentMethod::where('name',$value['bank'])->first();
            }

            $flightbookingpayment=FlightBookingPayment::updateOrCreate(
                [
                    'id_flight_book' => $flightbooking->id
                ],
                [
                    'midtrans_id' => $transaction_id,
                    'payment_method_id' => $payment->id,
                    'settlement_time' => $settlement_time,
                    'payment_status' => $transaction_status,
                    'va_number' => $va_number
                ]);
            return $flightbookingpayment;
        }
        else if($request->custom_field1=="JobAd"){
            $order_id = explode('-', $order_id);

            $transaction = JobVacancy::where('id', $order_id[2])->first();
            $company = Company::where('id', $transaction['id_company'])->first();

            $status = "";

            $token = [];
            $usertoken = PersonalAccessToken::select('name')->where('tokenable_id', $company['id_user'])->get();

            foreach($usertoken as $key => $value){
                array_push($token, $value->name); 
            }

            if($transaction_status=='settlement'){
                $status='success';
                $settlement_time=date("Y-m-d H:i:s",strtotime($request->settlement_time));
                if($transaction['package'] == 2){
                    $set_time = new DateTime($settlement_time);
                    $newtime = date_add($set_time, date_interval_create_from_date_string('30 days'));

                    $update_expiry = JobVacancy::where('id', $order_id[2])->update([
                        'deleted_at' => $newtime
                    ]);

                }
                else if($transaction['package'] == 3){
                    $set_time = new DateTime($settlement_time);
                    $newtime = date_add($set_time, date_interval_create_from_date_string('60 days'));

                    $update_expiry = JobVacancy::where('id', $order_id[2])->update([
                        'deleted_at' => $newtime
                    ]);
                }
                foreach ($token as $key => $value) {
                    NotificationController::sendPush($company['id_user'],$value, "Payment successful", "Your payment for ".$transaction['position']."ad is successful", "Job", "");
                }
            }
            else if($transaction_status=='pending'){
                $settlement_time=null;
            }
            else if($transaction_status=='expire'){
                $status='unsuccess';
                $settlement_time=null;
            }
            else if($transaction_status=='cancel'){
                $status='unsuccess';
                $settlement_time=null;

                foreach ($token as $key => $value) {
                    NotificationController::sendPush($company['id_user'],$value, "Job Ad Cancelled", "Your transaction for ".$transaction['position']."ad is successfuly cancelled", "Job", "");
                }
            }

            foreach ($request['va_numbers'] as $key => $value) {
                $va_number=$value['va_number'];
                $payment=PaymentMethod::where('name',$value['bank'])->first();
            }


            $vacancy_payment=JobVacancyPayment::where('id_vacancy', $order_id[2])->update([
                'midtrans_id' => $transaction_id,
                'payment_method_id' => $payment->id,
                'settlement_time' => $settlement_time,
                'payment_status' => $transaction_status,
                'va_number' => $va_number
            ]);

            $vacancy_data=JobVacancy::where('id', $order_id[2])->update([
                'status' => $status
            ]);

            $job_payment = JobVacancyPayment::where('id_vacancy', $order_id[2])->first();
            return $job_payment;
        }

    }

    public function espayCheckBalance()
    {
        $datetime=Date('Y-m-d H:i:s');
        $time = Date('YmdHms');

        $uuid="HAINAAPPcheckbalance".$time;

        //$uuid=$request->uuid;
        $sender_id="HAINAAPP";
        $password="zclwXJlnApNbBhYF";
        $current_date = new DateTime();
        $signature=hash('sha256',strtoupper("##".$sender_id."##".$uuid."##djHKvcScStINUlaK##"),false);

        $body=[
            "rq_uuid"       => $uuid,
            "rq_datetime"   => $datetime,
            "sender_id"     => $sender_id,
            "password"      => $password,
            "signature"     => $signature
        ];

        try {
            $response=$this->clientbalance->request(
                'POST',
                'getbalance',
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
                    'uuid'=>$uuid,
                    'request'=>json_encode($body),
                    'response'=>$bodyresponse,
                    'error_code'=>json_decode($bodyresponse)->error_code,   
                    'url'=>$url,
                    'response_code'=>$response->getStatusCode(),
                ]
            );
            return json_decode($bodyresponse)->balance;
        }catch(RequestException $e) {
            echo Psr7\Message::toString($e->getRequest());
            if ($e->hasResponse()) {
                echo Psr7\Message::toString($e->getResponse());
            }
            return;
        }
    }

    public function espayPayment($order_id)
    {
        $transaction=Transaction::where('order_id',$order_id)->with('product')->first();
        $datetime=Date('Y-m-d H:i:s');
        $time = Date('YmdHms');

        $uuid="HAINAAPP".$order_id."inq".$time;

        //$uuid=$request->uuid;
        $sender_id="HAINAAPP";
        $password="zclwXJlnApNbBhYF";
        $amount=($transaction->total_payment-$transaction->profit)*100;
        $current_date = new DateTime();
        $signature=hash('sha256',strtoupper("##".$sender_id."##".$transaction->customer_number."##".$transaction->product->product_code."##".$amount."##".$uuid."##djHKvcScStINUlaK##"),false);

        if($this->espayCheckBalance()<$amount/100){
            AdminAlert::create([
                "alert_type" => "balance",
                "message" => "espay balance insufficent",
                "datetime" => $datetime,
                "solved" => 0
            ]);
            return 0;
        }else{

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
                else if($bill->error_code == 800){
                    AdminAlert::create([
                        "alert_type" => "balance",
                        "message" => "espay balance insufficent",
                        "datetime" => $datetime,
                        "solved" => 0
                    ]);
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
}
