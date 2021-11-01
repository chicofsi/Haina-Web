<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestaurantBookmark extends Model
{

    use HasFactory;

    protected $table = 'restaurant_bookmark';

    public $timestamps = false;

    protected $fillable = ['user_id', 'restaurant_id'];

}