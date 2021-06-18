<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotelDarmaBookingPaxes extends Model
{

    use HasFactory;

    protected $table = 'hotel_darma_paxes_list';
    public $timestamps = false;

    protected $fillable = [ 
        'booking_id', 'title', 'first_name', 'last_name' 
    ];

    public function booking(){
        return $this->belongsTo('App\Models\HotelDarmaBooking', 'booking_id', 'id');
    }

}