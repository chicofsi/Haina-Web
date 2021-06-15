<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotelDarma extends Model
{

    use HasFactory;

    protected $table = 'hotel_darma';

    public $timestamps = false;

    protected $fillable = [ 
        'hotel_name', 'hotel_address', 'hotel_phone', 'city_id', 'hotel_website', 'hotel_email', 'hotel_rating', 'hotel_long', 'hotel_lat' 
    ];

    public function city(){
        return $this->hasOne('App\Models\City', 'id', 'city_id');
    }

    public function room(){
        return $this->hasMany('App\Models\HotelDarmaRoom', 'hotel_id');
    }

    public function image(){
        return $this->hasMany('App\Models\HotelDarmaImage', 'hotel_id');
    }

    public function facilities(){
        return $this->belongsToMany(HotelDarmaFacilitiesList::class, 'hotel_darma_facilities', 'hotel_id', 'facilities_id');
    }

}