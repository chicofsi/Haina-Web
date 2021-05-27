<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ProductCategoryResource;
use Illuminate\Support\Facades\URL;


class CategoryServiceResource extends JsonResource {

public function toArray($request){

    $product=[];
    foreach($this->productCategory as $key => $value){
        $product[$key]=new ProductCategoryResource($value);
    }

    return [
        'id'=>$this->id,
        'name'=>$this->name,
        'category'=>$product,
    ];
}

}
