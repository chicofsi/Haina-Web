<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlightAddons extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'flight_addons';

    protected $fillable = [ 
        'id_flight_passenger', 'baggage', 'seat', 'compartment'
    ];
    public $timestamps = false;

    public function flightpassanger(){
        return $this->belongsTo('App\Models\FlightPassenger', 'id_flight_passenger', 'id');
    }

    
    public function meal(){
    	return $this->hasMany('App\Models\FlightAddonsMeal','id_flight_addons','id');
    }
    
}
