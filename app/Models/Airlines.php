<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Airlines extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'airlines';
    protected $primaryKey = null;
    public $incrementing = false;
    protected $fillable = [
        'airline_code', 'airline_name', 'image'
    ];

    public $timestamps = false;

    public function flightbookingdetails(){
     return $this->hasMany('App\Models\FlightBookingDetails','airline_code','airline_code');
    }
}
