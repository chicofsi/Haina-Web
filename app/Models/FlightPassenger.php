<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlightPassenger extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'flight_passenger';

    protected $fillable = [ 
        'id_flight_book_detail', 'id_passenger', 'price',
    ];
    public $timestamps = false;

    public function flightbookingdetails(){
        return $this->belongsTo('App\Models\FlightBookingDetails', 'id_flight_book_detail', 'id');
    }

    public function passenger(){
        return $this->belongsTo('App\Models\Passenger', 'id_passenger', 'iata');
    }
    
    public function flightaddons(){
    	return $this->hasMany('App\Models\FlightAddons','id_flight_passenger','id');
    }
    
}
