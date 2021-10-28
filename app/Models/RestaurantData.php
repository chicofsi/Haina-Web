<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestaurantData extends Model
{

    use HasFactory;

    protected $table = 'restaurant_data';

    //public $timestamps = false;

    protected $fillable = ['name', 'address', 'detail_address', 'latitude', 'longitude', 'phone', 'user_id', 
    'open_days', 'weekdays_time_open', 'weekdays_time_close', 'weekend_time_open', 
    'weekend_time_close', 'open', 'halal'];

    public function cuisine(){
        //return $this->hasOne('App\Models\RestaurantCuisineType', 'id', 'cuisine_type_id');
        return $this->belongsToMany(RestaurantCuisineType::class, 'restaurant_cuisine_data', 'restaurant_id', 'cuisine_type_id');
    }

    public function type(){
        //return $this->hasOne('App\Models\RestaurantType', 'id', 'restaurant_type_id');
        return $this->belongsToMany(RestaurantType::class, 'restaurant_type_data', 'restaurant_id', 'restaurant_type_id');
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