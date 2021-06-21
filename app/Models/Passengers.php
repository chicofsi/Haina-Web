<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Passangers extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'passengers';

    protected $fillable = [ 
        'user_id', 'title', 'first_name', 'last_name', 'date_of_birth', 'gender', 'type', 'id_number', 'nationality', 'birth_country', 'parent'
    ];
    public $timestamps = false;
    
    public function passangerpassport(){
        return $this->hasOne('App\Models\PassengerPassport','id_passenger','id');
    }
    public function flightpassenger(){
        return $this->hasMany('App\Models\FlightPassenger','id_passenger','id');
    }

    public function user(){
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }

    // public function room(){
    //     return $this->belongsTo('App\Models\HotelRoom', 'room_id', 'id');
    // }

    // public function users(){
    //     return $this->belongsTo('App\Models\User', 'user_id', 'id');
    // }

    // public function payment(){
    //     return $this->hasOne('App\Models\HotelBookingPayment', 'booking_id');
    // }
    
    // public function photo(){
    //     return $this->hasMany('App\Models\CompanyPhoto','id_company','id');
    // }
    // public function user(){
    //     return $this->belongsTo('App\Models\User','id_user','id');
    // } 
    
}
