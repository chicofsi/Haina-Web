<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotelDarmaBookingRoomReq extends Model
{

    use HasFactory;

    protected $table = 'hotel_darma_booking_room_req';
    public $timestamps = false;

    protected $fillable = [ 
        'id_booking_session', 'room_type', 'child_num', 'child_age', 'smoking_room', 'phone', 'email', 'request_description'
    ];

    public function session(){
        return $this->belongsTo('App\Models\HotelDarmaBookingSession', 'id_booking_session', 'id');
    }

    public function paxes(){
        return $this->hasMany('App\Models\HotelDarmaBookingPaxes', 'id_room_req');
    }
}
