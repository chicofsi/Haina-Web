<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotelDarmaBookingSession extends Model
{

    use HasFactory;

    protected $table = 'hotel_darma_booking_session';

    protected $fillable = [ 
        'user_id', 'pax_passport', 'country_id', 'city_id', 'check_in_date', 'check_out_date', 'hotel_id', 'room_id', 'internal_code', 'breakfast', 'agent_os_ref'
    ];

    public function users(){
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }

    public function payment(){
        return $this->hasOne('App\Models\HotelDarmaBookingRoomReq', 'id_booking_session');
    }

}