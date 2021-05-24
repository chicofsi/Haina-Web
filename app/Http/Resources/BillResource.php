<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductGroup;

class BillResource extends JsonResource {

public function toArray($request){

    $product_group = Product::select('id_product_group')->where('product_code',$this->product_code)->get();

    $product_category = ProductGroup::select('id_product_category')->where('id', $product_group)->get();

    $product_type = ProductCategory::where('id', $product_category)->get();
    
    return [
        'rq_uuid' => $this->rq_uuid,
        'rs_datetime' => $this->rs_datetime,
        'error_code' => $this->error_code,
        'error_desc' => $this->error_desc,
        'order_id' => $this->error_desc,
        'bill_amount' => $this->bill_amount,
        'admin_fee' => $this->admin_fee,
        'name' => $product_type->name,
        'name_zh' => $product_type->name_zh,
        'icon_code' => $product_type->icon_code,
        'data' => $this->data
    ];
}
}