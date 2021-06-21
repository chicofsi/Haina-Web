<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FLightDetailsSession extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'flight_details_session';

    protected $fillable = [ 
        'id_flight_booking_session','type', 'airline_code', 'depart_from', 'depart_to', 'depart_date', 'arrival_date', 'total_passanger'
    ];

    public function flighttripsession(){
        return $this->hasMany('App\Models\FlightTripSession','id_flight_details_session','id');
    }
}
