<?php

namespace App\Http\Controllers\Pulsa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ValueMessage;
use App\Http\Resources\BillResource;
use App\Http\Resources\CategoryServiceResource;
use App\Http\Resources\ProductGroupResource;
use App\Http\Resources\InquiryBills as InquiryBillsResource;
use App\Http\Resources\PendingTransactionResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Collection;

use GuzzleHttp\Client;
use GuzzleHttp\TransferStats;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use App\Models\HotelBooking;
use App\Models\Transaction;
use App\Models\TransactionInquiry;
use App\Models\TransactionPayment;
use App\Models\Providers;
use App\Models\ProvidersPrefix;
use App\Models\User;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductGroup;
use App\Models\PaymentMethod;
use App\Models\PaymentMethodCategory;
use App\Models\CategoryService;
use App\Models\EspayRequest;

use App\Models\HotelDarma;
use App\Models\HotelDarmaBooking;
use App\Models\HotelDarmaPayment;
use App\Models\Company;
use App\Models\JobVacancy;
use App\Models\JobVacancyPackage;
use App\Models\JobVacancyPayment;

use DateTime;


class PulsaController extends Controller
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
    public function getProductCategory(Request $request)
    {
        $category=ProductCategory::get();
        if($category->isEmpty()){
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Category Doesn\'t Exist!','data'=> '']), 404);
        }
        else{
            return response()->json(new ValueMessage(['value'=>1,'message'=>'Product Category Found!','data'=> $category]), 200);
        }
    }

    public function getProductGroup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_product_category' => 'required'
        ]);

        if ($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 400);                        
        }else{
            $group=ProductGroup::where('id_product_category',$request->id_product_category)->get();

            foreach($group as $key => $value){
                $groupData[$key] = new ProductGroupResource($value);
            }

            if($group->isEmpty()){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Product Group Doesn\'t Exist!','data'=> '']), 404);
            }else{
                return response()->json(new ValueMessage(['value'=>1,'message'=>'Product Group Found!','data'=> $groupData]), 200);
            }
        }
    }

    public function getServiceCategory(Request $request)
    {
        $post = CategoryService::with('productCategory');

        $post = $post->get();

        if($post->isEmpty()){
            return response()->json(new ValueMessage(['value'=>0, 'message'=> 'Get Category Service Failed', 'data'=>'']), 404);
        } else {
            foreach($post as $key => $value){
                $postData[$key] = new CategoryServiceResource($value);
            }
        return response()->json(new ValueMessage(['value'=>1, 'message'=> 'Get Category Service Success', 'data'=> $postData]), 200);
        }        
    }

    public function getProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_product_group' => 'required'
        ]);

        if ($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 400);                        
        }else{
            $product=Product::where('id_product_group',$request->id_product_group)->get();

            if($product->isEmpty()){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Product Doesn\'t Exist!','data'=> '']), 404);
            }else{
                return response()->json(new ValueMessage(['value'=>1,'message'=>'Product Found!','data'=> $product]), 200);
            }
        }
    }

    public function getInquiryBills(Request $request)
    {
        $product=Product::where('product_code',$request->product_code)->first();

        if($product->inquiry_type=="inquiry"){
            $datetime=Date('Y-m-d H:i:s');
            $time = Date('YmdHms');

            $uuid="HAINAAPP".$request->order_id."inq".$time;

            //$uuid=$request->uuid;
            $sender_id="HAINAAPP";
            $password="zclwXJlnApNbBhYF";
            $current_date = new DateTime();
            $signature=hash('sha256',strtoupper("##".$sender_id."##".$request->order_id."##".$request->product_code."##".$uuid."##djHKvcScStINUlaK##"),false);
            

            $body=[
                "rq_uuid"       => $uuid,
                "rq_datetime"   => $datetime,
                "sender_id"     => $sender_id,
                "password"      => $password,
                "order_id"      => $request->order_id,
                "product_code"  => $request->product_code,
                "signature"     => $signature,
            ];
            try {
                $checkfive = Transaction::where('customer_number', $request->order_id)->where('id_product', $product['id'])->orderBy('created_at', 'desc')->first();

                if($checkfive){
                    $startdate = new DateTime("now");
                    $checkdate = new DateTime($checkfive['created_at']);
                    $minutediff = $startdate->diff($checkdate);

                    $minutes = $minutediff->days * 24 * 60;
                    $minutes += $minutediff->h * 60;
                    $minutes += $minutediff->i;

                    if($minutes <= 5){
                        return response()->json(new ValueMessage(['value'=>0,'message'=>'Please wait 5 minutes for the same transaction!','data'=> '']), 401);
                    }
                    else{
                        $response=$this->client->request(
                            'POST',
                            'inquirytransaction',
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
                                'order_id'=>$request->order_id,
                                'uuid'=>$uuid,
                                'request'=>json_encode($body),
                                'response'=>$bodyresponse,
                                'error_code'=>json_decode($bodyresponse)->error_code,
                                'url'=>$url,
                                'response_code'=>$response->getStatusCode(),
                            ]
                        );
                        //return $response;

                        $bill = json_decode($bodyresponse);
                        $bill->product_code = $request->product_code;

                        if(isset($bill->data->bill_period)){
                            $bill->data->bill_date = $bill->data->bill_period;
                            unset($bill->data->bill_period);
                        }
                        $product=Product::where('product_code',$request->product_code)->first();
                        
                        $billamount = $bill->amount/100;

                        $order_id=$this->generateOrderId();
                        $inquiry=TransactionInquiry::insert([
                            "id_product" => $product->id,
                            "order_id" => $order_id, 
                            "id_user" => $request->user()->id,
                            "amount" => $billamount,
                            "inquiry_data" => json_encode($bill->data)
                        ]);
                        $inquiry=TransactionInquiry::where('order_id',$order_id)->first();
                        $bill->inquiry = $inquiry->id;
                        if(isset($bill) && $bill->error_code == 0000){
                            $billdata = new InquiryBillsResource($bill);

                            return response()->json(new ValueMessage(['value'=>1,'message'=>'Bill Details Found!','data'=> $billdata]), 200);
                        }
                        else{
                            return response()->json(new ValueMessage(['value'=>0,'message'=>$bill->error_desc,'data'=> $bill->error_code]), 500);
                        }
                    }
                }
                else{
                    $response=$this->client->request(
                        'POST',
                        'inquirytransaction',
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
                            'order_id'=>$request->order_id,
                            'uuid'=>$uuid,
                            'request'=>json_encode($body),
                            'response'=>$bodyresponse,
                            'error_code'=>json_decode($bodyresponse)->error_code,
                            'url'=>$url,
                            'response_code'=>$response->getStatusCode(),
                        ]
                    );
                    //return $response;

                    $bill = json_decode($bodyresponse);
                    $bill->product_code = $request->product_code;

                    if(isset($bill->data->bill_period)){
                        $bill->data->bill_date = $bill->data->bill_period;
                        unset($bill->data->bill_period);
                    }
                    $product=Product::where('product_code',$request->product_code)->first();
                    
                    $billamount = $bill->amount/100;

                    $order_id=$this->generateOrderId();
                    $inquiry=TransactionInquiry::insert([
                        "id_product" => $product->id,
                        "order_id" => $order_id, 
                        "id_user" => $request->user()->id,
                        "amount" => $billamount,
                        "inquiry_data" => json_encode($bill->data)
                    ]);
                    $inquiry=TransactionInquiry::where('order_id',$order_id)->first();
                    $bill->inquiry = $inquiry->id;
                    if(isset($bill) && $bill->error_code == 0000){
                        $billdata = new InquiryBillsResource($bill);

                        return response()->json(new ValueMessage(['value'=>1,'message'=>'Bill Details Found!','data'=> $billdata]), 200);
                    }
                    else{
                        return response()->json(new ValueMessage(['value'=>0,'message'=>$bill->error_desc,'data'=> $bill->error_code]), 500);
                    }
                }
            }catch(RequestException $e) {
                echo Psr7\Message::toString($e->getRequest());
                if ($e->hasResponse()) {
                    echo Psr7\Message::toString($e->getResponse());
                }
                return;
            }
        }
        else{

            $checkfive = Transaction::where('customer_number', $request->order_id)->where('id_product', $product['id'])->orderBy('created_at', 'desc')->first();

            if($checkfive){
                $startdate = new DateTime("now");
                $checkdate = new DateTime($checkfive['created_at']);
                $minutediff = $startdate->diff($checkdate);

                $minutes = $minutediff->days * 24 * 60;
                $minutes += $minutediff->h * 60;
                $minutes += $minutediff->i;

                if($minutes <= 5){
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Please wait 5 minutes for the same transaction!','data'=> '']), 401);
                }
                else{
                    $data=(object)[
                        "product_code"=>$product->product_code,
                        "data"=>[
                            "customer_id"      => $request->order_id,
                            "product_code"  => $request->product_code,
                            "bill_date"     => date("m-d"),
                        ],
                        "rs_datetime"=>date("Y-m-d H:i:s"),
                        "inquiry"=>0,
                    ];
                    $billdata = new InquiryBillsResource($data);

                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Bill Details Found!','data'=> $billdata]), 200);
                }
            }else{
                
                $data=(object)[
                    "product_code"=>$product->product_code,
                    "data"=>[
                        "customer_id"      => $request->order_id,
                        "product_code"  => $request->product_code,
                        "bill_date"     => date("m-d"),
                    ],
                    "rs_datetime"=>date("Y-m-d H:i:s"),
                    "inquiry"=>0,
                ];
                $billdata = new InquiryBillsResource($data);

                return response()->json(new ValueMessage(['value'=>1,'message'=>'Bill Details Found!','data'=> $billdata]), 200);
            }
        }
    }

    public function getAmountBills(Request $request)
    {
        
        $datetime=Date('Y-m-d H:i:s');
        $time = Date('YmdHis');

        $uuid="HAINAAPP".$request->order_id."inq".$time;

        //$uuid=$request->uuid;
        $sender_id="HAINAAPP";
        $password="zclwXJlnApNbBhYF";
        $current_date = new DateTime();
        $signature=hash('sha256',strtoupper("##".$sender_id."##".$request->order_id."##".$request->product_code."##".$uuid."##djHKvcScStINUlaK##"),false);
        

        $body=[
            "rq_uuid"       => $uuid,
            "rq_datetime"   => $datetime,
            "sender_id"     => $sender_id,
            "password"      => $password,
            "order_id"      => $request->order_id,
            "product_code"  => $request->product_code,
            "signature"     => $signature,
        ];
        try {
  
            $response=$this->client->request(
                'POST',
                'inquirytransaction',
                [
                    'form_params' => $body
                ]  
            );
            //return $response;

            $bill = json_decode($response->getBody()->getContents());
            $bill->product_code = $request->product_code;

            if(isset($bill->data->bill_period)){
                $bill->data->bill_date = $bill->data->bill_period;
                unset($bill->data->bill_period);
            }

            if(isset($bill) && $bill->error_code == 0000){
                $billdata = new BillResource($bill);

                return response()->json(new ValueMessage(['value'=>1,'message'=>'Bill Details Found!','data'=> $billdata]), 200);
            }
            else{
                return response()->json(new ValueMessage(['value'=>0,'message'=>$bill->error_desc,'data'=> $bill->error_code]), 500);
            }   
        }catch(RequestException $e) {
            echo Psr7\Message::toString($e->getRequest());
            if ($e->hasResponse()) {
                echo Psr7\Message::toString($e->getResponse());
            }
            return;
        }
    }

    public function getDirectBills(Request $request)
    {
        $datetime=Date('Y-m-d H:i:s');
        $time = Date('YmdHms');

        $uuid="HAINAAPP".$request->order_id."inq".$time;

        //$uuid=$request->uuid;
        $sender_id="HAINAAPP";
        $password="zclwXJlnApNbBhYF";
        $amount=$request->amount;
        $current_date = new DateTime();
        $signature=hash('sha256',strtoupper("##".$sender_id."##".$request->order_id."##".$request->product_code."##".$amount."##".$uuid."##djHKvcScStINUlaK##"),false);
        

        $body=[
            "rq_uuid"       => $uuid,
            "rq_datetime"   => $datetime,
            "sender_id"     => $sender_id,
            "password"      => $password,
            "order_id"      => $request->order_id,
            "product_code"  => $request->product_code,
            "amount"        => $amount,
            "signature"     => $signature
        ];
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
                    'order_id'=>$request->order_id,
                    'uuid'=>$uuid,
                    'request'=>json_encode($body),
                    'response'=>$bodyresponse,
                    'error_code'=>json_decode($bodyresponse)->error_code,   
                    'url'=>$url,
                    'response_code'=>$response->getStatusCode(),
                ]
            );
            //return $response;

            $bill = json_decode($bodyresponse);
            //return $response;
            
            if(isset($bill) && $bill->error_code == 0000){
                $bill->product_code = $request->product_code;
                //dd($bill);
                $bill->data->amount = intval($bill->data->amount);            

                //if($amount != $bill->data->amount){
                //    $bill->amount = $bill->data->amount;
                //}

                $bill->amount = intval($amount);

                $billdata = new BillResource($bill);
                
                return response()->json(new ValueMessage(['value'=>1,'message'=>'Bill Details Found! '.$bill->error_code,'data'=> $billdata]), 200);
            }
            else if($bill->error_code == 610){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Wait 5 minutes','data'=> '']), 500);
            }
            else if($bill->error_code == 9999){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Suspect/Timeout','data'=> '']), 500);
            }
            else if($bill->error_code == 802){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Max/min payment amount exceeded','data'=> '']), 500);
            }
            else{
                return response()->json(new ValueMessage(['value'=>0,'message'=>$bill->error_desc,'data'=> $bill->error_code]), 500);
            }

        }catch(RequestException $e) {
            echo Psr7\Message::toString($e->getRequest());
            if ($e->hasResponse()) {
                echo Psr7\Message::toString($e->getResponse());
            }
            return;
        }
    }

    public function getProviders(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'number' => 'required'
        ]);

        if ($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 400);                        
        }else{
            $prefix=substr($request->number, 0, 4);
            $providerspref=ProvidersPrefix::with('providers')->where('prefix',$prefix)->first();

            if($providerspref){
                $provider=Providers::where('id',$providerspref->providers->id)->first();

                $data['provider']=$provider;

                $productgrouppulsa=ProductGroup::where('id_providers',$provider->id)->where('id_product_category',1)->with('product')->first();

                if(!$productgrouppulsa){
                    $data['group']['pulsa']="not available";
                }else{
                    if($productgrouppulsa->product->isEmpty()){
                        $data['group']['pulsa']="not available";

                    }
                    foreach ($productgrouppulsa->product as $key => $value) {
                        $data['group']['pulsa'][$key]=$value;
                    }
                    
                }

                $productgroupdata=ProductGroup::where('id_providers',$provider->id)->where('id_product_category',2)->with('product')->get();

                if($productgroupdata->isEmpty()){
                    $data['group']['data']=[];
                }else{
                    foreach ($productgroupdata as $key => $value) {
                        if($value->product->isEmpty()){
                            continue;
                            $data['group']['data'][$key]['name']=$value->name;
                            $data['group']['data'][$key]['product']="Product Not Available!";
                        }else{
                            $data['group']['data'][$key]['name']=$value->name;
                            
                            foreach ($value->product as $k => $v) {
                                $data['group']['data'][$key]['product'][$k]=$v;
                            }
                        }
                        
                    }
        //          if($productgroupdata->product->isEmpty()){
        //              $data['group']['data']="not available";

        //          }
                    // foreach ($productgroupdata->product as $key => $value) {
        //              $data['group']['data'][$key]=$value;
        //          }
                }

                return response()->json(new ValueMessage(['value'=>1,'message'=>'Providers Found!','data'=> $data]), 200);
            }
            else{
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Provider Doesn\'t Exist!','data'=> '']), 404);
                
            }
        }
    }
    public function getInquiry(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_code' => 'required',
            'customer_number' => 'required'
        ]);

        if ($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 400);                        
        }else{
            $datetime=Date('Y-m-d H:i:s');
            $time = Date('YmdHis');

            $uuid="HAINAAPP".$request->customer_number."inq".$time;

            //$uuid=$request->uuid;
            $sender_id="HAINAAPP";
            $password="zclwXJlnApNbBhYF";
            $current_date = new DateTime();
            $signature=hash('sha256',strtoupper("##".$sender_id."##".$request->customer_number."##".$request->product_code."##".$uuid."##djHKvcScStINUlaK##"),false);
            

            $body=[
                "rq_uuid"       => $uuid,
                "rq_datetime"   => $datetime,
                "sender_id"     => $sender_id,
                "password"      => $password,
                "order_id"      => $request->customer_number,
                "product_code"  => $request->product_code,
                "signature"     => $signature,
            ];
            try {
                $response=$this->client->request(
                    'POST',
                    'inquirytransaction',
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
                        'order_id'=>$request->customer_number,
                        'uuid'=>$uuid,
                        'request'=>json_encode($body),
                        'response'=>$bodyresponse,
                        'error_code'=>json_decode($bodyresponse)->error_code,
                        'url'=>$url,
                        'response_code'=>$response->getStatusCode(),
                    ]
                );
                //return $response;

                $bill = json_decode($bodyresponse);
                $bill->product_code = $request->product_code;
                if(isset($bill->data->bill_period)){
                    $bill->data->bill_date = $bill->data->bill_period;
                    unset($bill->data->bill_period);
                }
                $product=Product::where('product_code',$request->product_code)->first();
                
                $billamount = $bill->amount/100;
                
                $order_id=$this->generateOrderId();
                $inquiry=TransactionInquiry::insert([
                    "id_product" => $product->id,
                    "order_id" => $order_id, 
                    "id_user" => $request->user()->id,
                    "amount" => $billamount,
                    "inquiry_data" => json_encode($bill->data)
                ]);
                $inquiry=TransactionInquiry::where('order_id',$order_id)->first();
                if(isset($bill) && $bill->error_code == 0000){

                    $product=Product::where('product_code',$request->product_code)->first();
                    $product->sell_price=($bill->amount/100)+$product->sell_price-$product->base_price;
                    $product->id_inquiry=$inquiry->id;
                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Inquiry Success!','data'=> $product]), 200);
                }
                else{
                    return response()->json(new ValueMessage(['value'=>0,'message'=>$bill->error_desc,'data'=> $bill->error_code]), 500);
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

    public function getPaymentMethod(Request $request)
    {
        $paymentmethod=PaymentMethodCategory::with('paymentmethod')->get();

        return response()->json(new ValueMessage(['value'=>1,'message'=>'Get Payment Method Success!','data'=> $paymentmethod]), 200);

    }
    function generateOrderId() {
        $randomString = '';
        do{
            $length = 10;
            $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $charactersLength = strlen($characters);
            for ($i = 0; $i < $length; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }

        }while (Transaction::where('order_id',$randomString)->first()||TransactionInquiry::where('order_id',$randomString)->first());
        
        return $randomString;
    }

    public function chargeMidtrans($transaction,$payment)
    {
        $username="SB-Mid-server-uUu-OOYw1hyxA9QH8wAbtDRl";
        $url="https://api.sandbox.midtrans.com/v2/charge";
        $data_array =  array(
            "payment_type"          => $payment->category->url,
            "bank_transfer"         => array(
                "bank"              => $payment->name
            ),
            "custom_field1"        => "PPOB",
            "transaction_details"   => array(
                "order_id"          => $transaction->order_id,
                "gross_amount"      => $transaction->total_payment
            ),
        );

        $header="Authorization: Basic ".base64_encode($username.":");
        // return json_encode($data_array)."BLABLABLAB".$header."davdavd".$username.":";
        $make_call = $this->callAPI($url, json_encode($data_array),$header);
        return $make_call;
    }

    public function cancelMidtrans($transaction, $payment, $type)
    {
        if($type == "PPOB"){
            $id = $transaction['order_id'];
        }
        else if($type == "JobAd"){
            $get_id = JobVacationPayment::where('id_vacancy',$transaction['id'])->first();

            $id = $get_id['order_id'];
        }
         

        $username="SB-Mid-server-uUu-OOYw1hyxA9QH8wAbtDRl";
        $url="https://api.sandbox.midtrans.com/v2/".$id."/cancel";
        $data_array =  array(
            "payment_type"          => $payment->category->url,
            "bank_transfer"         => array(
                "bank"              => $payment->name
            ),
            "custom_field1"        => $type,
            "transaction_details"   => array(
                "order_id"          => $transaction->order_id,
                "gross_amount"      => $transaction->total_payment
            ),
        );

        $header="Authorization: Basic ".base64_encode($username.":");
        // return json_encode($data_array)."BLABLABLAB".$header."davdavd".$username.":";
        $make_call = $this->callAPI($url, json_encode($data_array),$header);
        return $make_call;
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

    public function createTransaction($iduser, $productcode, $customernumber, $payment, $id_inquiry)
    {
        if(User::where('id',$iduser)->first()){
            if(Product::where('product_code',$productcode)->first()){
                $product=Product::where('product_code',$productcode)->first();
                $inquiry=TransactionInquiry::where('id',$id_inquiry)->first();
                $transaction=Transaction::create([
                    'id_user' => $iduser,
                    'order_id' => $inquiry->order_id,
                    'transaction_time' => date("Y-m-d H:i:s"),
                    'total_payment' => $inquiry->amount+($product->sell_price - $product->base_price),
                    'profit' => ($product->sell_price - $product->base_price),
                    'status' => 'pending payment',
                    'id_product' => $product->id,
                    'customer_number' => $customernumber
                ]);
                $transaction['payment_data']=json_decode($this->chargeMidtrans($transaction,$payment));
                return $transaction;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    public function createBillTransaction($iduser, $product_code, $amount, $customernumber, $payment, $order_id)
    {
        if(User::where('id',$iduser)->first()){
            if(Product::where('product_code',$product_code)->first()){
                $product=Product::where('product_code',$product_code)->first();
                $transaction=Transaction::create([
                    'id_user' => $iduser,
                    'order_id' => $order_id,
                    'transaction_time' => date("Y-m-d H:i:s"),
                    'total_payment' => $amount,
                    'profit' => $product->sell_price,
                    'status' => 'pending payment',
                    'id_product' => $product->id,
                    'customer_number' => $customernumber
                ]);
                $transaction['payment_data']=json_decode($this->chargeMidtrans($transaction,$payment));
                return $transaction;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    public function addTransaction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_code' => 'required',
            'customer_number' => 'required',
            'id_payment_method' => 'required',
            'id_inquiry' => 'required'
        ]);

        if ($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 400);                        
        }else{

            $payment=PaymentMethod::where('id',$request->id_payment_method)->with('category')->first();
            $checkfive = Transaction::where('customer_number', $request->customer_number)->orderBy('created_at', 'desc')->first();

            if($checkfive){
                $startdate = new DateTime("now");
                $checkdate = new DateTime($checkfive['created_at']);
                $minutediff = $startdate->diff($checkdate);

                $minutes = $minutediff->days * 24 * 60;
                $minutes += $minutediff->h * 60;
                $minutes += $minutediff->i;

                if($minutes <= 5){
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Please wait 5 minutes for the same transaction!','data'=> '']), 401);
                }
                else{
                    $transaction = $this->createTransaction($request->user()->id, $request->product_code, $request->customer_number, $payment, $request->id_inquiry);
                    if($transaction){
                        $transaction_data=Transaction::where('id',$transaction->id)->with('product')->first();
                        $data['payment_type']=$transaction->payment_data->payment_type;
                        $data['amount']=$transaction->payment_data->gross_amount;
                        $data['payment_status']=$transaction->payment_data->transaction_status;
                        foreach ($transaction->payment_data->va_numbers as $key => $value) {
                            $data['virtual_account']=$value->va_number;
                            $data['bank']=$value->bank;
                        }
                        $transaction_data['payment']=$data;

                        return response()->json(new ValueMessage(['value'=>1,'message'=>'Transaction Success!','data'=> $transaction_data]), 200);
                    }else {
                        return response()->json(new ValueMessage(['value'=>0,'message'=>'Transaction Failed!','data'=> ""]), 400);

                    }
                }
            }
            else{
                $transaction = $this->createTransaction($request->user()->id, $request->product_code, $request->customer_number, $payment, $request->id_inquiry);
                if($transaction){
                    $transaction_data=Transaction::where('id',$transaction->id)->with('product')->first();
                    $data['payment_type']=$transaction->payment_data->payment_type;
                    $data['amount']=$transaction->payment_data->gross_amount;
                    $data['payment_status']=$transaction->payment_data->transaction_status;
                    foreach ($transaction->payment_data->va_numbers as $key => $value) {
                        $data['virtual_account']=$value->va_number;
                        $data['bank']=$value->bank;
                    }
                    $transaction_data['payment']=$data;

                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Transaction Success!','data'=> $transaction_data]), 200);
                }else {
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Transaction Failed!','data'=> ""]), 400);

                }
            }
            
        }
    }

    public function addBillsTransaction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_code' => 'required',
            'customer_number' => 'required',
            'id_payment_method' => 'required'
        ]);

        if ($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 400);                        
        }else{
            $payment=PaymentMethod::where('id',$request->id_payment_method)->with('category')->first();

            $product=Product::where('product_code',$request->product_code)->first();

            if($product->inquiry_type=="inquiry"){
                $order_id=TransactionInquiry::where('id',$request->id_inquiry)->first()->order_id;
            }else{
                $order_id=$this->generateOrderId();
            }

            $transaction = $this->createBillTransaction($request->user()->id, $request->product_code, $request->amount, $request->customer_number, $payment,$order_id);
            if($transaction){
                $transaction_data=Transaction::where('id',$transaction->id)->with('product')->first();
                $data['payment_type']=$transaction->payment_data->payment_type;
                $data['amount']=$transaction->payment_data->gross_amount;
                $data['payment_status']=$transaction->payment_data->transaction_status;
                foreach ($transaction->payment_data->va_numbers as $key => $value) {
                    $data['virtual_account']=$value->va_number;
                    $data['bank']=$value->bank;
                }
                $transaction_data['payment']=$data;
                $transaction_payment = TransactionPayment::create([
                    'id_transaction' => $transaction_data->id,
                    'id_payment_method' => $request->id_payment_method,
                    'midtrans_id' => '',
                    'va_number' => $transaction_data->payment['virtual_account'],
                    'settlement_time' => null,
                    'payment_status' => 'pending'
                ]);


                return response()->json(new ValueMessage(['value'=>1,'message'=>'Transaction Success!','data'=> $transaction_data]), 200);
            }else {
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Transaction Failed!','data'=> ""]), 400);

            }
        }
    }

    public function cancelTransaction(Request $request){
        $validator = Validator::make($request->all(), [
            'id_transaction' => 'required_without_all:id_vacancy',
            'id_vacancy' => 'required_without_all:id_transaction'
        ]);

        if ($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 400);                        
        }else{
            if($request->id_transaction != null){
                $get_transaction = Transaction::where('id', $request->id_transaction)->first();

                if($get_transaction){
                    $get_payment_data = TransactionPayment::where('id_transaction', $get_transaction['id'])->first();

                    /*
                    $update_payment = TransactionPayment::where('id_transaction', $get_transaction['id'])->update([
                        'payment_status' => 'cancel'
                    ]);

                    $update_transaction = Transaction::where('id', $request->id_transaction)->update([
                        'status' => 'unsuccess'
                    ]);
                    */

                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Transaction cancelled!','data'=> $get_transaction]), 200);
                }
                else{
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Transaction not found!','data'=> ""]), 404);
                }
            }
            else{
                $get_vacancy = JobVacancy::where('id', $request->id_vacancy)->where('status', 'not like', 'unsuccess')->where('package', '!=', 1)->first();

                if($get_vacancy){
                    $get_payment_data = JobVacancyPayment::where('id_vacancy', $request->id_vacancy)->with('vacancy')->first();
                    $payment = PaymentMethod::where('id',$get_payment_data['payment_method_id'])->with('category')->first();

                    $cancel = json_decode($this->cancelMidtrans($get_vacancy, $payment, "JobAd"));

                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Transaction cancelled!','data'=> $cancel]), 200);
                }
                else{
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Job transaction not found!','data'=> ""]), 404);
                }
            }

        }
    }

    public function transactionList(Request $request)
    {
        $pending=Transaction::where('id_user',$request->user()->id)->with('product','payment')->where('status','pending payment')->orWhere('status','process')->get();
        $success=Transaction::where('id_user',$request->user()->id)->with('product','payment')->where('status','success')->get();
        $cancel=Transaction::where('id_user',$request->user()->id)->with('product','payment')->where('status','unsuccess')->get();

        $pending_list=[];
        $success_list=[];
        $cancel_list=[];
        $pending_list_job=[];
        $success_list_job=[];
        $cancel_list_job=[];

        foreach($pending as $key => $value){
            $value->transaction_time = date('Y-m-d\TH:i:s.u\Z' , strtotime($value->transaction_time));
            array_push($pending_list, $value);
        }
        foreach($success as $key => $value){
            $value->transaction_time = date('Y-m-d\TH:i:s.u\Z' , strtotime($value->transaction_time));
            array_push($success_list, $value);
        }
        foreach($cancel as $key => $value){
            $value->transaction_time = date('Y-m-d\TH:i:s.u\Z' , strtotime($value->transaction_time));
            array_push($cancel_list, $value);
        }
        
    
        $check_owner = Company::where('id_user', Auth::id())->first();
        
        if($check_owner){
            $get_vacancy = JobVacancy::where('id_company', $check_owner['id'])->where('package', '!=', 1)->get();

            foreach($get_vacancy as $key => $value){
                $get_payment = JobVacancyPayment::where('id_vacancy', $value->id)->with('vacancy')->first();
                //dd($get_payment);
                $ad_list = new \stdClass();
    
                if($get_payment){
                    $package_name = JobVacancyPackage::where('id', $value->package)->first();
                    $payment_name = $get_payment['payment_method_id'];
                    $payment_cat = PaymentMethod::select('id_payment_method_category')->where('id', $payment_name)->first();
                    $payment_method = PaymentMethodCategory::select('name')->where('id', $payment_cat['id_payment_method_category'])->first();
        
                    $ad_list = (object)[
                        'id_vacancy' => $get_payment['id_vacancy'],
                        'category' => "Job Ad (".$package_name['name'].")",
                        'order_id' => $get_payment['order_id'],
                        'transaction_time' => date('Y-m-d\TH:i:s.u\Z' , strtotime($get_payment['created_at'])),
                        'product' => $value->position." Ad ".$package_name['name'],
                        'package' => $package_name['name'],
                        'total_payment' => $get_payment['price'],
                        'status' => $get_payment['payment_status'],
                        'icon' => '&#xf0f2;',
                        'id_payment_method' => $get_payment['payment_method_id'],
                        'payment_method' => $payment_method['name'],
                        'va_number' => $get_payment['va_number'],
                        'midtrans_id' => $get_payment['midtrans_id']
                    ];

                    if($get_payment['payment_status'] == 'pending'){
                        array_push($pending_list_job, $ad_list);
                    }
                    else if($get_payment['payment_status'] == 'settlement'){
                        array_push($success_list_job, $ad_list);
                    }
                    else if($get_payment['payment_status'] == 'cancel' || $get_payment['payment_status'] == 'expire'){
                        array_push($cancel_list_job, $ad_list);
                    }
                }
                  
            }

            $transaction['pending_job']=$pending_list_job;
            //$transaction['process']=$process;
            $transaction['success_job']=$success_list_job;
            $transaction['canceled_job']=$cancel_list_job;
        }
        

        $transaction['pending']=$pending_list;
        $transaction['success']=$success_list;
        $transaction['canceled']=$cancel_list;
        
        
        return response()->json(new ValueMessage(['value'=>1,'message'=>'Get Transaction List Success!','data'=> $transaction]), 200);
    
    }

    public function pendingTransactionList(Request $request){
        //logo, nama produk, total amount, metode pembayaran
        $list_pending = [];
        $bill_pending=Transaction::where('id_user',$request->user()->id)->with('product','payment')->where('status','pending payment')->get();
        
        foreach($bill_pending as $key => $value){
            //dd($value);
            $bill_list = new PendingTransactionResource($value);
            array_push($list_pending, $bill_list);
        }
        
        //dd($bill_list);

        $hotel_pending=HotelDarmaBooking::where('user_id',$request->user()->id)->with('hotel', 'payment')->where('status','UNPAID')->get();

        foreach($hotel_pending as $key => $value){
            $hotel_list = new PendingTransactionResource($value);
            array_push($list_pending,$hotel_list);
        }
        
        //$date = array_column($list_pending, 'transaction_date');
        //$amount = array_column($list_pending, 'total_amount');
        //array_multisort($date, SORT_ASC, $amount, SORT_DESC, $list_pending);

        $check_owner = Company::where('id_user', Auth::id())->first();
        $get_vacancy = JobVacancy::where('id_company', $check_owner['id'])->where('package', '!=', 1)->get();

        foreach($get_vacancy as $key => $value){
            $get_payment = JobVacancyPayment::where('id_vacancy', $value->id)->with('vacancy')->first();

            if($get_payment && $get_payment['payment_status'] == 'pending'){

                $package_name = JobVacancyPackage::where('id', $value->package)->first();
                $payment_name = $get_payment['payment_method_id'];
                $payment_cat = PaymentMethod::select('id_payment_method_category')->where('id', $payment_name)->first();
                $payment_method = PaymentMethodCategory::select('name')->where('id', $payment_cat['id_payment_method_category'])->first();

                $ad_list = (object)[
                    'order_id' => $get_payment['order_id'],
                    'transaction_time' => date('Y-m-d\TH:i:s.u\Z' , strtotime($get_payment['created_at'])),
                    'product' => $value->position." Ad ".$package_name['name'],
                    'total_amount' => $get_payment['price'],
                    'status' => $get_payment['payment_status'],
                    'icon' => '&#xf0f2;',
                    'id_payment_method' => $get_payment['payment_method_id'],
                    'payment_method' => $payment_method['name']
                ];
                
                array_push($list_pending, $ad_list);
            }
        }

        //dd($list_pending);

        usort($list_pending, function($a, $b) {
            return strcmp($b->transaction_time, $a->transaction_time);
        });

        if(isset($list_pending)){
            return response()->json(new ValueMessage(['value'=>1,'message'=>'Get Transaction List Success!','data'=> $list_pending]), 200);
        }
        else{
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Error in getting transaction!','data'=> '']), 404);
        }
        
    }

}
