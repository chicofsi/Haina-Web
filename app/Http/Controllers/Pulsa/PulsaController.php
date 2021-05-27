<?php

namespace App\Http\Controllers\Pulsa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ValueMessage;
use App\Http\Resources\BillResource;
use App\Http\Resources\CategoryServiceResource;
use App\Http\Resources\ProductGroupResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use App\Models\Transaction;
use App\Models\Providers;
use App\Models\ProvidersPrefix;
use App\Models\User;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductGroup;
use App\Models\PaymentMethod;
use App\Models\PaymentMethodCategory;
use App\Models\CategoryService;

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

	public function getAmountBills(Request $request)
    {
        
        $datetime=Date('Y-m-d H:m:s');
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

            $billdata = new BillResource($bill);

            return response()->json(new ValueMessage(['value'=>1,'message'=>'Bill Details Found!','data'=> $billdata]), 200);
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
        
        $datetime=Date('Y-m-d H:m:s');
        $time = Date('YmdHms');

        $uuid="HAINAAPP".$request->order_id."inq".$time;

        //$uuid=$request->uuid;
        $sender_id="HAINAAPP";
        $password="zclwXJlnApNbBhYF";
        $amount=1000000;
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
                    'form_params' => $body
                ]  
            );
            //return $response;

            $bill = json_decode($response->getBody()->getContents());
            
            if(isset($bill) && $bill->error_code != 610){
                $bill->product_code = $request->product_code;

                $bill->data->amount = intval($bill->data->amount);            

                if($amount != $bill->data->amount){
                    $bill->amount = $bill->data->amount;
                }

                $billdata = new BillResource($bill);
                
                return response()->json(new ValueMessage(['value'=>1,'message'=>'Bill Details Found!','data'=> '']), 200);
            }
            else if($bill->error_code == 610){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Wait 5 minutes','data'=> '']), 500);
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
        // 			if($productgroupdata->product->isEmpty()){
        // 				$data['group']['data']="not available";

        // 			}
    				// foreach ($productgroupdata->product as $key => $value) {
        // 				$data['group']['data'][$key]=$value;
        // 			}
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
            'id_product' => 'required',
            'customer_number' => 'required'
        ]);

        if ($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 400);                        
        }else{
            $product=Product::where('id',$request->id_product)->first();
            return response()->json(new ValueMessage(['value'=>1,'message'=>'Inquiry Success!','data'=> $product]), 200);
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

		}while (Transaction::where('order_id',$randomString)->first());
		
		return $randomString;
	}
	public function chargeMidtrans($transaction,$payment)
	{
		$username="SB-Mid-server-uUu-OOYw1hyxA9QH8wAbtDRl";
		$url="https://api.sandbox.midtrans.com/v2/charge";
		$data_array =  array(
		    "payment_type"			=> $payment->category->url,
		    "bank_transfer"			=> array(
		    	"bank"				=> $payment->name
		    ),
		    "transaction_details"	=> array(
		        "order_id"			=> $transaction->order_id,
		        "gross_amount"		=> $transaction->total_payment
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

	public function createTransaction($iduser, $idproduct, $customernumber, $payment)
    {
    	if(User::where('id',$iduser)->first()){
    		if(Product::where('id',$idproduct)->first()){
    			$product=Product::where('id',$idproduct)->first();
    			$transaction=Transaction::create([
    				'id_user' => $iduser,
    				'order_id' => $this->generateOrderId(),
    				'transaction_time' => date("Y-m-d h:m:s"),
    				'total_payment' => $product->sell_price,
    				'profit' => ($product->sell_price - $product->base_price),
    				'status' => 'pending payment',
    				'id_product' => $idproduct,
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

    public function createBillTransaction($iduser, $product_code, $amount, $adminfee, $customernumber, $payment)
    {
    	if(User::where('id',$iduser)->first()){
    		if(Product::where('product_code',$product_code)->first()){
    			$product=Product::where('product_code',$product_code)->first();
    			$transaction=Transaction::create([
    				'id_user' => $iduser,
    				'order_id' => $this->generateOrderId(),
    				'transaction_time' => date("Y-m-d h:m:s"),
    				'total_payment' => $amount,
    				'profit' => $adminfee,
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
            'id_product' => 'required',
            'customer_number' => 'required',
            'id_payment_method' => 'required'
        ]);

        if ($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 400);                        
        }else{
            $payment=PaymentMethod::where('id',$request->id_payment_method)->with('category')->first();
        	$transaction = $this->createTransaction($request->user()->id, $request->id_product, $request->customer_number, $payment);
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

    public function transactionList(Request $request)
    {
        $pending=Transaction::where('id_user',$request->user()->id)->with('product','payment')->where('status','pending payment')->get();
        $process=Transaction::where('id_user',$request->user()->id)->with('product','payment')->where('status','process')->get();
        $success=Transaction::where('id_user',$request->user()->id)->with('product','payment')->where('status','success')->get();
        $cancel=Transaction::where('id_user',$request->user()->id)->with('product','payment')->where('status','unsuccess')->get();

        $transaction['pending']=$pending;
        $transaction['process']=$process;
        $transaction['success']=$success;
        $transaction['canceled']=$cancel;
        
        return response()->json(new ValueMessage(['value'=>1,'message'=>'Get Transaction List Success!','data'=> $transaction]), 200);
    
    }

}
