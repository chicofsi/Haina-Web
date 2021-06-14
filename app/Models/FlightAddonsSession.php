<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FLightAddonsSession extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'flight_addons_session';

    public $timestamps = false;

    protected $fillable = [ 
        'id_flight_trip_session', 'id_flight_passenger_session', 'baggage_string', 'seat', 'compartment', 'meals'
    ];

    public function flighttripsession(){
        return $this->belongsTo('App\Models\FlightTripSession', 'id_flight_trip_session', 'id');
    }
    
    public function flightpassengersession(){
        return $this->belongsTo('App\Models\FlightPassengerSession', 'id_flight_passenger_session', 'id');
    }

    
}
