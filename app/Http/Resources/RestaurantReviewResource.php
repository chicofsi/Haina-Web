<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;

use App\Models\User;
use App\Models\RestaurantData;
use App\Models\RestaurantReview;
use App\Models\RestaurantReviewPhotos;

use App\Http\Resources\ProductResource;

class RestaurantReviewResource extends JsonResource {

    public function toArray($request){
        $restaurant_name = RestaurantData::select('name')->where('id', $this->restaurant_id)->first();

        $review_photos = RestaurantReviewPhotos::where('review_id', $this->id)->get();

        $user = User::where('id', $this->user_id)->first();

        return [
            'id' => $this->id,
            'restaurant_id' => $this->restaurant_id,
            'restaurant_name' => $restaurant_name['name'],
            'user_id' => $this->user_id,
            'username' => $user['username'],
            'rating' => $this->rating,
            'review' => $this->review,
            'photos' => $review_photos,
            'review_date' => $this->created_at
        ];

    }
}