<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FLightTrip extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'flight_trip';

    protected $fillable = [ 
        'id_flight_booking_detail', 'airline_code', 'flight_number', 'origin', 'destination', 'detail_schedule', 'depart_time', 'arrival_time', 'flight_class', 'garuda_number', 'garuda_availability'
    ];
    protected $timestamps=false;

    public function flightbookingdetails(){
        return $this->belongsTo('App\Models\FlightBookingDetails', 'id_flight_booking_detail', 'id');
    }

    public function depart(){
        return $this->belongsTo('App\Models\Airports', 'origin', 'iata');
    }

    public function arrival(){
        return $this->belongsTo('App\Models\Airports', 'destination', 'iata');
    }

}
