<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethodCategory extends Model
{
    use HasFactory;

    protected $table = 'payment_method_category';

    protected $fillable = [
    	'name', 'url', 'photo_url'
    ];

    public function paymentmethod(){
        return $this->hasMany('App\Models\PaymentMethod','id_payment_method_category','id');
    } 
}
