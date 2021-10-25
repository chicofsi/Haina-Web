<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestaurantReview extends Model
{

    use HasFactory;

    protected $table = 'restaurant_review';

    protected $fillable = ['restaurant_id', 'user_id', 'rating', 'review'];

    public function review_image(){
        return $this->hasMany('App\Models\RestaurantReviewPhotos', 'review_id');
    }

}