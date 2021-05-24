<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ValueMessage;

class CurrencyController extends Controller
{
	  public $apiKey="d8b0130ab394427aa3c118a98716a1c7";

   	public function index(Request $request){

    	$url = "https://api.currencyfreaks.com/latest?apikey=".$this->apiKey;

   		$json = json_decode(file_get_contents($url), true);

   		$list=array();

   		foreach($json['rates'] as $key => $val) {
   			if($key==$request->base){
   				$basekey=$key;
   				$baserate=$val;
   			}
   		}

   		$multiplier=1/$baserate;

   		$json['base']=$request->base;
   		foreach($json['rates'] as $key => $val) {
        if($key=="IDR"||$key=="CNY"||$key=="USD"){
          $json['rates'][$key]=sprintf('%f', floatval(strval(round($val*$multiplier,5))));
        }else{
          unset($json['rates'][$key]);
        }
   		}
      
		  return response()->json(new ValueMessage(['value'=>1,'message'=>'Get List Success!','data'=> $json]), 200);
    }

    

    public function getList(){
    	$url = "https://api.currencyfreaks.com/latest?apikey=".$this->apiKey;

   		$json = json_decode(file_get_contents($url), true);

   		$list=array();

   		foreach($json['rates'] as $key => $val) {

			array_push($list, ["rates" => $key]);
   		}

		return response()->json(new ValueMessage(['value'=>1,'message'=>'Get List Success!','data'=> $list]), 200);

    }
}
