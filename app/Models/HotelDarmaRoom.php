<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotelDarmaRoom extends Model
{

    use HasFactory;

    protected $table = 'hotel_darma_room';

    public $timestamps = false;

    protected $fillable = [ 
        'hotel_id', 'room_name', 'room_type_id', 'room_image', 'room_price', 'breakfast'
    ];

    public function hotel(){
        return $this->belongsTo('App\Models\HotelDarma', 'id', 'hotel_id');
    }

    public function roomType(){
        return $this->hasOne('App\Models\HotelDarmaRoomType', 'id', 'room_type_id');
    }

    public function room_facilities(){
        return $this->belongsToMany(HotelDarmaRoomFacilitiesList::class, 'hotel_darma_room_facilities', 'hotel_room_id', 'facilities_id');
    }
    
}