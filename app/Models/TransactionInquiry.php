<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionInquiry extends Model
{
    use HasFactory;

    protected $table = 'transaction_inquiry';

    protected $fillable = [
    	 'id_user', 'order_id', 'id_product', 'amount', 'inquiry_data'
    ];


    public function user(){
    	return $this->belongsTo('App\Models\User','id_user','id');
    } 
    public function product(){
        return $this->belongsTo('App\Models\Product','id_product','id');
    } 
}
