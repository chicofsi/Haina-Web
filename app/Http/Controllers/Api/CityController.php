<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\City;
use App\Models\Province;
use App\Http\Resources\ValueMessage;

use GuzzleHttp\Client;
use GuzzleHttp\TransferStats;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use DateTime;

class CityController extends Controller
{
    public function getCity(Request $request){
        $city = City::all();

        if($request->name != null){
            $city = City::where('name', 'like', '%'.$request->name.'%')->get();
        }
        if($request->id_province != null){
            $city = $city->where('id_province', $request->id_province);
        }

        if(!$city || count($city) == 0){
            return response()->json(new ValueMessage(['value'=>0,'message'=>'City Not Found!','data'=> '']), 404);
        }
        else{
            return response()->json(new ValueMessage(['value'=>1,'message'=>'City Found!','data'=> $city]), 200);
        }

    }

    public function getProvince(Request $request){
        if($request->name != null){
            $province = Province::where('name', 'like', '%'.$request->name.'%')->get();
            dd($province);
            if(!$province || count($province) == 0){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'No Province Found!','data'=> '']), 404);
            }else{
                return response()->json(new ValueMessage(['value'=>1,'message'=>'Get Province Success!','data'=> $province]), 200);
            }
        }
        else{
            $province = Province::all();

            return response()->json(new ValueMessage(['value'=>1,'message'=>'Get Province Success!','data'=> $province]), 200);
        }

    }

}