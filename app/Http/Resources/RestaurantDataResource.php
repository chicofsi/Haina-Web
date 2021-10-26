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

        $cuisine = "";
        $cuisine_zh = "";
        $type = "";
        $type_zh = "";
        $rating = RestaurantReview::where('restaurant_id', $this->id)->avg('rating') ?? 0.0;

        foreach($this->cuisine as $key => $value){
            if($cuisine == ""){
                $cuisine = $value->name;
            }
            else{
                $cuisine = $cuisine.", ".$value->name;
            }

            if($cuisine_zh == ""){
                $cuisine_zh = $value->name_zh;
            }
            else{
                $cuisine_zh = $cuisine_zh.", ".$value->name_zh;
            }
        }

        foreach($this->type as $key => $value){
            if($type == ""){
                $type = $value->name;
            }
            else{
                $type = $type.", ".$value->name;
            }

            if($type_zh == ""){
                $type_zh = $value->name_zh;
            }
            else{
                $type_zh = $type_zh.", ".$type->name_zh;
            }
        }

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
            'cuisine' => $cuisine,
            'cuisine_zh' => $cuisine_zh,
            'type' => $type,
            'type_zh' => $type_zh,
            'verified' => $this->verified,
            'rating' => $rating
        ];

    }

}