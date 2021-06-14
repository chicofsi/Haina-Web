<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FLightBookingSession extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'flight_booking_session';

    protected $fillable = [ 
        'id_user', 'airline_id', 'trip_type', 'origin', 'destination', 'depart_date', 'return_date', 'pax_adult', 'pax_child', 'pax_infant', 'depart_reference', 'return_reference', 'contact_title', 'contact_first_name', 'contact_last_name', 'contact_country_code_phone', 'contact_area_code_phone', 'contact_remaining_phone_no', 'insurance', 'search_key'
    ];

    public function user(){
        return $this->belongsTo('App\Models\User', 'id_user', 'id');
    }

    public function flighttripsession(){
    	return $this->hasMany('App\Models\FlightTripSession','id_flight_booking_session','id');
    }

    public function flightpassengersession(){
        return $this->hasMany('App\Models\FlightPassengerSession','id_flight_booking_session','id');
    }
    
}
