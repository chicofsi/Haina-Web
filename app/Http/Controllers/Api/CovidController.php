<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ValueMessage;

class CovidController extends Controller
{
    public function index(){
    	$url = "https://indonesia-covid-19.mathdro.id/api/provinsi/";

   		$json = json_decode(file_get_contents($url), true);

   		$dataJKT=$json["data"][0];


		return response()->json(new ValueMessage(['value'=>1,'message'=>'Get Data Jakarta Success!','data'=> $dataJKT]), 200);
    }
    public function all(){
    	$url = "https://indonesia-covid-19.mathdro.id/api/provinsi/";

   		$json = json_decode(file_get_contents($url), true);



		return response()->json(new ValueMessage(['value'=>1,'message'=>'Get Data Success!','data'=> $json["data"]]), 200);
    }
}
