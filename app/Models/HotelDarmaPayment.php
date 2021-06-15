<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotelDarmaPayment extends Model
{
    use HasFactory;

    protected $table = 'hotel_darma_payment';

    protected $fillable = [ 
        'booking_id', 'payment_method_id', 'midtrans_id', 'va_number', 'settlement_time', 'payment_status'
    ];

    public function booking(){
        return $this->belongsTo('App\Models\HotelDarmaBooking', 'id', 'booking_id');
    }

    public function paymentMethod(){
        return $this->belongsTo('App\Models\PaymentMethod', 'id', 'payment_method_id');
    }

}