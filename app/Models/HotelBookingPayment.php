<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotelBookingPayment extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'hotel_booking_payment';

    protected $fillable = [ 
        'booking_id', 'payment_method_id', 'midtrans_id', 'va_number', 'settlement_time', 'payment_status'
    ];

    public function booking(){
        return $this->belongsTo('App\Models\Booking', 'id', 'booking_id');
    }

    public function paymentMethod(){
        return $this->belongsTo('App\Models\PaymentMethod', 'id', 'payment_method_id');
    }
    
    /*
    
    //public function jobvacancy(){
    	return $this->hasMany('App\Models\JobVacancy','id_company','id');
    }
    public function address(){
        return $this->hasMany('App\Models\CompanyAddress','id_company','id');
    }
    public function photo(){
        return $this->hasMany('App\Models\CompanyPhoto','id_company','id');
    }
    public function user(){
        return $this->belongsTo('App\Models\User','id_user','id');
    } 
    */
}
