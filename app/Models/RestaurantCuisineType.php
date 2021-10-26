<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestaurantCuisineType extends Model
{

    use HasFactory;

    protected $table = 'restaurant_cuisine_type';

    public $timestamps = false;

    protected $fillable = ['name', 'name_zh'];

}