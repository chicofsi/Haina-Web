<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlightAddonsMeal extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'flight_addons_meal';

    protected $primaryKey = null;
    public $incrementing = false;
    protected $fillable = [ 
        'meal','id_flight_addons'
    ];
    public $timestamps = false;

    public function flightaddons(){
        return $this->belongsTo('App\Models\FlightAddons', 'id_flight_addons', 'id');
    }

    
}
