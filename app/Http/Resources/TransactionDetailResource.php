<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;

use App\Models\User;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductGroup;
use App\Models\TransactionPayment;
use App\Models\PaymentCategory;
use App\Models\PaymentMethod;

class TransactionDetailResource extends JsonResource {

    public function toArray($request){

    $user = User::where('id', $this->id_user)->first();

    $user_data = [
        'id' => $user['id'],
        'fullname' => $user['fullname'],
        'email' => $user['email'],
        'phone' => $user['phone'],
        'username' => $user['username']
    ];

    $product = Product::where('id', $this->id_product)->first();
    $product_group = ProductGroup::where('id', $product['id_product_group'])->first();
    $product_category = ProductCategory::where('id', $product_group['id_product_category'])->first();

    $providers = null;
    if($product_group['id_providers'] != null){
        $get_provider = Providers::where('id', $product_group['id_providers'])->first();
        $providers = [
            'id' => $get_provider['id'],
            'name' => $get_provider['name'],
            'photo_url' => "https://hainaservice.com/storage/".$get_provider['photo_url']
        ];
    }

    $product_data = [
        'id' => $product['id'],
        'id_product_group' => $product['id_product_group'],
        'product_group' => $product_group['name'],
        'id_product_category' => $product_category['id'],
        'product_category' => $product_category['name'],
        'product_code' => $product['product_code'],
        'product_description' => $product['descripiton'],
        'inquiry_type'=> $product['inquiry_type'],
        'providers' => $providers
    ];

    $payment = TransactionPayment::where('id_transaction', $this->id)->first();
    $payment_method = PaymentMethod::where('id', $payment['id_payment_method'])->first();
    $payment_category = PaymentMethodCategory::where('id', $payment_method['id_payment_method_category'])->first();

    $payment_data = [
        'id' => $payment['id'],
        'id_transaction' => $this->id,
        'midtrans_id' => $payment['midtrans_id'],
        'id_payment_method' => $payment['id_payment_method'],
        'payment_method' => $payment_method['name'],
        'payment_method_photo' => $payment_method['photo_url'],
        'id_payment_method_category' => $payment_category['id'],
        'payment_method_category' => $payment_category['name'],
        'settlement_time' => $payment['settlement_time'],
        'payment_status' => $payment['payment_status'],
        'va_number' => $payment['va_number'],
    ];

    return [
        'id' => $this->id,
        'order_id' => $this->order_id,
        'transaction_time' => $this->transaction_time,
        'total_payment' => $this->total_payment,
        'status' => $this->status,
        'customer_number' => $this->customer_number,
        'user_data' => $user_data,
        'product_data' => $product_data,
        'payment_data' => $payment_data
    ];
    }
}