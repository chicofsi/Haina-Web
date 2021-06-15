<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotelDarmaBookingPaxes extends Model
{

    use HasFactory;

    protected $table = 'hotel_darma_booking_paxes';

    protected $fillable = [ 
        'id_room_req', 'title', 'first_name', 'last_name' 
    ];

    public function room_req(){
        return $this->belongsTo('App\Models\HotelDarmaBookingRoomReq', 'id_room_req', 'id');
    }

}