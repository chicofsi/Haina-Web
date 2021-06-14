<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FLightPassengerSession extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'flight_passenger_session';

    public $timestamps = false;

    protected $fillable = [ 
        'id_flight_booking_session', 'id_number', 'title', 'first_name', 'last_name', 'birth_date', 'gender', 'nationality', 'birth_country', 'parent', 'passport_number', 'passport_issued_date', 'passport_issued_country', 'passport_expired_date', 'type'
    ];

    public function flightbookingsession(){
        return $this->belongsTo('App\Models\FlightBookingSession', 'id_flight_booking_session', 'id');
    }

    public function flightaddonssession(){
    	return $this->hasMany('App\Models\FlightAddonsSession','id_flight_passenger_session','id');
    }
    
}
