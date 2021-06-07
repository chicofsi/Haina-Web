<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ValueMessage;
use App\Http\Resources\HowToPayResource;
use App\Models\HowToPay;

class HowToPayController extends Controller
{
    public function instruction(Request $request){
        $instruction = HowToPay::where('id_payment_method', $request->id_payment_method)->get();


        if(isset($instruction)){
            $howto = new HowToPayResource($instruction);

            return response()->json(new ValueMessage(['value'=>1,'message'=>'Get Payment Instructions Success!','data'=> $howto]), 200);

        }
        else{
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Get Payment Instructions Failed!','data'=> '']), 404);
        }
    }
}