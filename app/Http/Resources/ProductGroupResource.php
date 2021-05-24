<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;

use App\Models\Product;

use App\Http\Resources\ProductResource;

class ProductGroupResource extends JsonResource {

    public function toArray($request){

        //$photo=URL::to('storage/'.$this->photo_url);

        $product=[];
        foreach($this->product as $key => $value){
            $product[$key] = new ProductResource($value);
        }
        
        return [
            'id'=>$this->id,
            'id_product_category'=>$this->id_product_category,
            'name'=>$this->name,
            'photo_url'=>$this->photo_url,
            'id_providers'=>$this->id_providers,
            'product'=>$product[0]['product_code']
        ];
    }
}