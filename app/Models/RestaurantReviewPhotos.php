<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestaurantReviewPhotos extends Model
{

    use HasFactory;

    protected $table = 'restaurant_review_photos';

    protected $fillable = ['review_id', 'photo_url'];


}