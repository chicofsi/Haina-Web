<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;
use App\Models\City;

class CompanyAddress extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $city=City::where('id',$this->id_city)->first();
        return [
            'id'=>$this->id,
            'address'=>$this->address,
            'status'=>$this->active,
            'id_city'=>$city->id,
            'city'=>$city->name,
            'primary_address'=>this->primary_address
        ];
    }
}
