<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;

use App\Models\Hotel;
use App\Models\HotelBooking;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductGroup;
use App\Models\PaymentMethod;
use App\Models\PaymentMethodCategory;

class PendingTransactionResource extends JsonResource {

    public function toArray($request){
        $data=$this;

        if(isset($this->hotel)){
            $hotel_name = Hotel::select('name')->where('id', $this->hotel->id)->first();

            $payment_method = HotelBookingPayment::select('id_payment_method_category')->where('id', $this->payment->id_payment_method)->first();
            $payment_name = PaymentMethodCategory::select('name')->where('id', $payment_method['id_payment_method_category'])->first();
            
            $name = "Booking at ".$hotel_name['name'];
            $icon = "&#1f3e8;";
            $payment = $payment_name['name'];
        }
        else if(isset($this->product)){
            
            $product_group = Product::select('id_product_group', 'description')->where('id',$this->product->id)->first();
            $product_category = ProductGroup::select('id_product_category')->where('id', $product_group['id_product_group'])->first();
            $product_type = ProductCategory::where('id', $product_category['id_product_category'])->first();

            //$payment_method = PaymentMethod::select('id_payment_method_category')->where('id', $this->payment->id_payment_method)->first();
            //$payment_name = PaymentMethodCategory::select('name')->where('id', $payment_method['id_payment_method_category'])->first();

            $name = $product_group['description'];
            $icon = $product_type['icon_code'];
            $payment = "Virtual";
        }
        
        // return [
        //     'order_id' => $this->order_id,
        //     'transaction_time' => $this->transaction_time,
        //     'product' => $name,
        //     'total_amount' => $this->total_amount,
        //     'customer_number' => $this->customer_number,
        //     'status' => $this->status,
        //     'icon' => $icon,
        //     'id_payment_method' => $data->payment->id_payment_method,
        //     'payment_method' => $payment
            
        // ];
        
        return $this->payment;
    }
}