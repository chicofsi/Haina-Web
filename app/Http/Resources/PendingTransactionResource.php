<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;

use App\Models\Hotel;
use App\Models\HotelBooking;
use App\Models\HotelBookingPayment;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\ProductCategory;
use App\Models\ProductGroup;
use App\Models\PaymentMethod;
use App\Models\PaymentMethodCategory;
use App\Models\TransactionPayment;

use App\Models\HotelDarma;
use App\Models\HotelDarmaBooking;
use App\Models\HotelDarmaPayment;

class PendingTransactionResource extends JsonResource {

    public function toArray($request){
        //$data=$this;
        if(isset($this->hotel)){
            $transaction=HotelDarmaBooking::where('id',$this->id)->with('hotel','payment')->first();

            $hotel_name = HotelDarma::select('hotel_name')->where('id', $transaction->hotel->id)->first();

            $payment_id = HotelDarmaPayment::select('payment_method_id')->where('id',$transaction->payment->id)->first();
            $payment_method = PaymentMethod::select('id_payment_method_category')->where('id', $payment_id['payment_method_id'])->first();
            $payment_name = PaymentMethodCategory::select('name')->where('id', $payment_method['id_payment_method_category'])->first();
            
            $name = "Booking at ".$hotel_name['hotel_name'];
            $category = 0;
            $icon = "&#xf594;";
            $payment = $payment_name['name'];
            $total_amount = $this->total_price;
            $id_payment_method = $payment_id['payment_method_id'];
        }
        else if(isset($this->product)){

            $transaction=Transaction::where('id',$this->id)->with('product','payment')->first();
            
            $product_group = Product::select('id_product_group', 'description')->where('id',$transaction->product->id)->first();
            $product_category = ProductGroup::select('id_product_category')->where('id', $product_group['id_product_group'])->first();
            $product_type = ProductCategory::where('id', $product_category['id_product_category'])->first();
            if($transaction->payment!=null){

                $payment_id = TransactionPayment::select('id_payment_method')->where('id',$transaction->payment->id)->first();
                $payment_method = PaymentMethod::select('id_payment_method_category')->where('id', $payment_id['id_payment_method'])->first();
                $payment_name = PaymentMethodCategory::select('name')->where('id', $payment_method['id_payment_method_category'])->first();

                $payment = $payment_name['name'];
                $id_payment_method = $payment_id['id_payment_method'];
            }else{

                $payment ="";
                $id_payment_method =0;
            }

            $name = $product_group['description'];
            $icon = $product_type['icon_code'];
            $category = $product_category['id_product_category'];
            //$payment = "Virtual";
            $total_amount = $this->total_payment;
            $number = $this->customer_number;
        }
        
        return [
            'order_id' => $this->order_id,
            'transaction_time' => $this->created_at,
            'product' => $name,
            'id_category' => $category,
            'total_amount' => $total_amount,
            'customer_number' => $this->number,
            'status' => $this->status,
            'icon' => $icon,
            'id_payment_method' => $id_payment_method,
            'payment_method' => $payment
            
        ];
        
        //return $this->payment->id_payment_method;
    }
}
