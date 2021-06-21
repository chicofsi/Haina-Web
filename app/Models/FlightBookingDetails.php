<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FLightBookingDetails extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'flight_booking_details';

    protected $fillable = [ 
        'id_flight_book', 'airline_code', 'depart_from', 'depart_to', 'depart_date', 'arrival_date', 'pnr'
    ];

    public function airlines(){
        return $this->belongsTo('App\Models\Airlines', 'airline_code', 'airline_code');
    }

    public function depart(){
        return $this->belongsTo('App\Models\Airports', 'depart_from', 'iata');
    }

    public function arrival(){
        return $this->belongsTo('App\Models\Airports', 'depart_to', 'iata');
    }

    public function flightbooking(){
        return $this->belongsTo('App\Models\FlightBooking', 'id_flight_book', 'id');
    }
    
    public function flightpassenger(){
    	return $this->hasMany('App\Models\FlightPassenger','id_flight_book_detail','id');
    }

    public function flighttrip(){
        return $this->hasMany('App\Models\FlightTrip','id_flight_booking_detail','id');
    }
    
}
