<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;

use App\Models\RestaurantMenu;
use App\Models\RestaurantReview;
use App\Models\RestaurantReviewPhotos;

use App\Http\Resources\ProductResource;

class RestaurantDataResource extends JsonResource {

    public function toArray($request){

        //$menu = RestaurantMenu::where('restaurant_id', $this->id)->with('menu_image')->get();

        $rating = RestaurantReview::avg('rating')->where('restaurant_id', $this->id);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'city_id' => $this->city_id,
            'phone' => $this->phone,
            'user_id' => $this->user_id,
            'open_days' => $this->open_days,
            'weekdays_time_open' => $this->weekdays_time_open,
            'weekdays_time_close' => $this->weekdays_time_close,
            'weekend_time_open' => $this->weekend_time_open,
            'weekend_time_close' => $this->weekend_time_close,
            'halal' => $this->halal,
            'verified' => $this->verified,
            'rating' => $rating
        ];

    }

}