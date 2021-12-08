<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;
use App\Models\City;

class CompanyMedia extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $photo=URL::to('storage/'.$this->media_url);
        //$city=City::where('id',$this->id_city)->first();
        return [
            'id'=>$this->id,
            'name'=>$this->name,
            'media_url'=>$photo
        ];
    }
}
