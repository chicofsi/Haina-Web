<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Airports extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'airports';
    protected $fillable = [
        'city', 'name', 'country','iata', 'icao', 'lat', 'lon', 'alt', 'timezone', 'dst', 'timezone_olson'
    ];

    public $timestamps = false;


    public function depart(){
     return $this->hasMany('App\Models\FlightBookingDetails','depart_from','iata');
    }

    public function arrival(){
     return $this->hasMany('App\Models\FlightBookingDetails','depart_to','iata');
    }
}
