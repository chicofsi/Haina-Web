<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductGroup;

class BillResource extends JsonResource {

public function toArray($request){

    $product_group = Product::select('id_product_group', 'description')->where('product_code',$this->product_code)->first();
    
    $product_category = ProductGroup::select('id_product_category')->where('id', $product_group['id_product_group'])->first();

    $product_type = ProductCategory::where('id', $product_category['id_product_category'])->first();

    if(isset($this->bill_amount)){
        $billamount = $this->bill_amount;
        $adminfee = $this->admin_fee;
    }
    else{
        $billamount = $this->amount;
        $adminfee = 0;
    }
    
    return [
        'rq_uuid' => $this->rq_uuid,
        'rs_datetime' => $this->rs_datetime,
        'error_code' => $this->error_code,
        'error_desc' => $this->error_desc,
        'order_id' => $this->order_id,
        'bill_amount' => $billamount,
        'admin_fee' => $adminfee,
        'product' => $product_group['description'],
        'category' => $product_type['name'],
        'category_zh' => $product_type['name_zh'],
        'icon_code' => $product_type['icon_code'],
        'bill_data' => $this->data
    ];
}
}