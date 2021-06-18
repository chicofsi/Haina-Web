<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotelDarmaRoomFacilitiesList extends Model
{
    use HasFactory;

    protected $table = 'hotel_darma_room_facilities_list';

    public $timestamps = false;

    protected $fillable = [ 
        'name'
    ];

    public function room(){
        return $this->belongsToMany(HotelDarmaRoom::class, 'hotel_darma_room_facilities', 'facilities_id', 'hotel_room_id');
    }

}