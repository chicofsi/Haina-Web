<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotelDarmaRoomType extends Model
{
    use HasFactory;

    protected $table = 'hotel_darma_room_type';


    protected $fillable = [ 
        'name', 'max_guest'
    ];

}