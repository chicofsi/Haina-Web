<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;

class ProductResource extends JsonResource {

    public function toArray($request){

        //$photo=URL::to('storage/'.$this->photo_url);
        
        return [
            'id' => $this->id,
            'product_code' => $this->product_code,
            'description' => $this->description
        ];
    }
}