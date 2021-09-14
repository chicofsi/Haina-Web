<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotelDarmaBooking extends Model
{

    use HasFactory;

    protected $table = 'hotel_darma_booking';

    protected $fillable = [ 
        'hotel_id', 'room_id', 'user_id', 'reservation_no', 'agent_os_ref', 'booking_date', 'check_in', 'check_out',
        'total_price', 'requests', 'breakfast', 'status', 'cancelation_policy'
    ];

    public function hotel(){
        return $this->belongsTo('App\Models\HotelDarma', 'hotel_id', 'id');
    }

    public function room(){
        return $this->belongsTo('App\Models\HotelDarmaRoom', 'room_id', 'id');
    }

    public function users(){
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }

    public function payment(){
        return $this->hasOne('App\Models\HotelDarmaPayment', 'booking_id');
    }

    public function request(){
        return $this->belongsToMany(HotelDarmaRequestList::class, 'hotel_darma_requests', 'booking_id', 'request_id');
    }

    public function paxes(){
        return $this->hasMany('App\Models\HotelDarmaPaxesList', 'booking_id');
    }
}