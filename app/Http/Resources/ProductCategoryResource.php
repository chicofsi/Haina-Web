<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;

class ProductCategoryResource extends JsonResource {

public function toArray($request){
    
    return [
        'id'=>$this->id,
        'name'=>$this->name,
        'name_zh'=>$this->name_zh,
        'icon'=>$this->icon_code,
        'id_service_category'=>$this->id_service_category,
    ];
}
}