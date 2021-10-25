<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestaurantPhotos extends Model
{

    use HasFactory;

    protected $table = 'restaurant_photos';

    protected $fillable = ['restaurant_id', 'photo_url'];


}