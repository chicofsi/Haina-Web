<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $table = 'payment_method';

    protected $fillable = [
    	 'id_payment_method_category', 'name', 'photo_url'
    ];


    public function payment(){
        return $this->hasMany('App\Models\TransactionPayment','id_payment_method','id');
    } 

    public function category(){
        return $this->belongsTo('App\Models\PaymentMethodCategory','id_payment_method_category','id');
    } 
}
