<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlightClass extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'flight_class';

    protected $fillable = [
        'name', 'code'
    ];

    public $timestamps = false;

    public function flightbookingdetails(){
     return $this->hasMany('App\Models\FlightBookingDetails','id_class','id');
    }
}
