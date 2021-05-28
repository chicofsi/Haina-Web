<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionPayment extends Model
{
    use HasFactory;

    protected $table = 'transaction_payment';

    protected $fillable = [
    	 'id_transaction', 'midtrans_id', 'id_payment_method', 'settlement_time', 'payment_status', 'va_number'
    ];


    public function transaction(){
    	return $this->belongsTo('App\Models\Transaction','id_transaction','id');
    } 
    public function paymentmethod(){
    	return $this->belongsTo('App\Models\PaymentMethod','id_payment_method','id');
    } 
}
