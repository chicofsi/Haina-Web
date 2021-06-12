<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlightBookingPayment extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'flight_booking_payment';

    protected $fillable = [ 
        'id_flight_book', 'payment_method_id', 'midtrans_id', 'va_number', 'settlement_time', 'payment_status'
    ];

    public function booking(){
        return $this->belongsTo('App\Models\FlightBook', 'id', 'id_flight_book');
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
