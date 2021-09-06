<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FLightTripSession extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'flight_trip_session';

    public $timestamps = false;

    protected $fillable = [ 
        'id_flight_details_session', 'type', 'airline_code', 'flight_number', 'sch_origin', 'sch_destination', 'detail_schedule', 'sch_depart_time', 'sch_arrival_time', 'garuda_number', 'garuda_availability'
    ];

    public function flighttripsession(){
        return $this->belongsTo('App\Models\FlightTripSession', 'id_flight_trip_session', 'id');
    }

    public function flightaddonssession(){
    	return $this->hasMany('App\Models\FlightAddonsSession','id_flight_trip_session','id');
    }
    
}
