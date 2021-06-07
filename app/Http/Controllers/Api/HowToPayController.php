<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ValueMessage;
use App\Http\Resources\HowToPayResource;
use App\Models\HowToPay;
use App\Models\PaymentMethod;

class HowToPayController extends Controller
{
    public function instruction(Request $request){
        $instruction = HowToPay::where('id_payment_method', $request->id_payment_method)->get();


        if(isset($instruction)){
            $howto_data = null;
            
            //$howto = new HowToPayResource($instruction);

            $payment_name = PaymentMethod::select('name')->where('id',$request->id_payment_method)->first();
            $payment_icon = PaymentMethod::select('photo_url')->where('id',$request->id_payment_method)->first();

            $howto_data['payment'] = $payment_name['name'];
            $howto_data['icon'] = $payment_icon['photo_url'];
            $howto_data['how_to'] = $instruction;

            return response()->json(new ValueMessage(['value'=>1,'message'=>'Get Payment Instructions Success!','data'=> $howto_data]), 200);

        }
        else{
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Get Payment Instructions Failed!','data'=> '']), 404);
        }
    }
}