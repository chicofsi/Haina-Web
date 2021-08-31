<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FLightBooking extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'flight_book';

    protected $fillable = [ 
        'order_id', 'id_user', 'trip_type', 'customer_email', 'amount', 'status', 'booking_date', 'airline_booking_code', 'timelimit'
    ];


    public function payment(){
        return $this->hasOne('App\Models\FlightBookingPayment', 'id_flight_book', 'id');
    }
    
    public function flightbookingdetails(){
    	return $this->hasMany('App\Models\FlightBookingDetails','id_flight_book','id');
    }

    public function flightcontact(){
        return $this->hasMany('App\Models\FlightContact','id_flight_book','id');
    }
    public function user(){
        return $this->belongsTo('App\Models\User','id_user','id');
    } 


    
}
