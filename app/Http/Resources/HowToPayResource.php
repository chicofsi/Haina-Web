<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\PaymentMethod;

class HowToPayResource extends JsonResource{
    
    public function toArray($request)
    {

        $payment_method = PaymentMethod::select('name')->where('id',$this->id_payment_method);

        $method = [];
        foreach($this as $key => $value){
            $method[$key] = $value;
        }

        return[
            'payment_method' => $payment_method,
            'how_to' => $method
        ];
    }
}