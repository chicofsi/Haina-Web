<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotelDarmaRequestList extends Model
{

    use HasFactory;

    protected $table = 'hotel_darma_request_list';
    public $timestamps = false;

    protected $fillable = [ 
        'id', 'hotel_id', 'description'
    ];

    public function hotel(){
        return $this->belongsTo('App\Models\HotelDarma', 'hotel_id', 'id');
    }

    /*
    public function request(){
        return $this->belongsToMany(HotelDarmaBooking::class, 'hotel_darma_requests', 'request_id', 'booking_id');
    }
    */

}