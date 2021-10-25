<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestaurantMenu extends Model
{

    use HasFactory;

    protected $table = 'restaurant_menu';

    protected $fillable = ['restaurant_id', 'menu_name'];

    public function menu_image(){
        return $this->hasMany('App\Models\RestaurantMenuPhotos', 'menu_id');
    }

}