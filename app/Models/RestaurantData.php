<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestaurantData extends Model
{

    use HasFactory;

    protected $table = 'restaurant_data';

    //public $timestamps = false;

    protected $fillable = ['name', 'address', 'latitude', 'longitude', 'city_id', 'phone', 'user_id', 'cuisine_type_id',
    'restaurant_type_id', 'open_days', 'weekdays_time_open', 'weekdays_time_close', 'weekend_time_open', 
    'weekend_time_close', 'halal'];

    public function cuisine(){
        return $this->hasOne('App\Models\RestaurantCuisineType', 'id', 'cuisine_type_id');
    }

    public function type(){
        return $this->hasOne('App\Models\RestaurantType', 'id', 'restaurant_type_id');
    }

    public function image(){
        return $this->hasMany('App\Models\RestaurantPhotos', 'restaurant_id');
    }

    public function menu(){
        return $this->hasMany('App\Models\RestaurantMenu', 'restaurant_id');
    }

    public function owner(){
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }

    public function review(){
        return $this->hasMany('App\Models\RestaurantReview', 'restaurant_id');
    }

}