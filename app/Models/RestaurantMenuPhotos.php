<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestaurantMenuPhotos extends Model
{

    use HasFactory;

    protected $table = 'restaurant_menu_photos';

    protected $fillable = ['menu_id', 'photo_url'];


}