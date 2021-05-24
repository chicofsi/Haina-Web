<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transaction';

    protected $fillable = [
    	 'id_user', 'order_id', 'transaction_time', 'total_payment', 'profit', 'status', 'id_product', 'customer_number'
    ];


    public function user(){
    	return $this->belongsTo('App\Models\User','id_user','id');
    } 
    public function product(){
        return $this->belongsTo('App\Models\Product','id_product','id');
    } 
    
    public function payment(){
        return $this->hasOne('App\Models\TransactionPayment','id_transaction','id');
    } 
}
