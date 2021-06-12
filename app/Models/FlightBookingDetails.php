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
        'id_flight_book', 'airline_code', 'depart_from', 'depart_to', 'depart_date', 'arrival_date', 'depart_time', 'arrival_time', 'flight_number', 'pnr', 'total_passanger', 'id_class'
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

    public function class(){
        return $this->belongsTo('App\Models\FlightClass', 'id_class', 'id');
    }

    public function flightbooking(){
        return $this->belongsTo('App\Models\FlightBooking', 'id_flight_book', 'id');
    }
    
    public function flightpassanger(){
    	return $this->hasMany('App\Models\FlightPassanger','id_flight_book_detail','id');
    }
    
}
