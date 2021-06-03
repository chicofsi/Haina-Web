<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductGroup;

class InquiryBills extends JsonResource {

public function toArray($request){

    $product_group = Product::where('product_code',$this->product_code)->first();
    
    $product_category = ProductGroup::where('id', $product_group['id_product_group'])->first();

    $product_type = ProductCategory::where('id', $product_category['id_product_category'])->first();

    if($this->inquiry==1){
        if(isset($this->bill_amount)){
            $billamount = $this->bill_amount/100;
        }
        else{
            $billamount = $this->amount/100;
        }
    }else{
        
        $billamount=0;
    }

    if($this->product_code == "SLYTSD" && isset($this->data->phone_no)){
        $this->data->customer_id = $this->data->phone_no;
    }
    
    
    return [
        'datetime' => $this->rs_datetime,
        'bill_amount' => $billamount,
        'admin_fee' => $product_group->sell_price,
        'product' => $product_group['description'],
        'product_code' => $this->product_code,
        'category' => $product_type['name'],
        'category_zh' => $product_type['name_zh'],
        'icon_code' => $product_type['icon_code'],
        'bill_data' => $this->data,
        'inquiry' => $this->inquiry
    ];
}
}
