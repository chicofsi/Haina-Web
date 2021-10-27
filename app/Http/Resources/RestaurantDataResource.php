<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;

use App\Models\RestaurantMenu;
use App\Models\RestaurantPhotos;
use App\Models\RestaurantReview;
use App\Models\RestaurantReviewPhotos;

use App\Http\Resources\ProductResource;

class RestaurantDataResource extends JsonResource {

    public function toArray($request){

        //$menu = RestaurantMenu::where('restaurant_id', $this->id)->with('menu_image')->get();

        $cuisine_array = [];
        
        $type_array = [];
        
        $rating = RestaurantReview::where('restaurant_id', $this->id)->avg('rating') ?? 0.0;


        foreach($this->cuisine as $key => $value){
            $cuisine = new \stdClass();
            $cuisine->id = $value->id;
            $cuisine->name = $value->name;
            $cuisine->name_zh = $value->name_zh;

            array_push($cuisine_array, $cuisine);
        }

        foreach($this->type as $key => $value){
            $type = new \stdClass();
            
            $type->id = $type->id;
            $type->name = $type->name;
            $type->name_zh = $type->name_zh;

            array_push($type_array, $type);
        }

        $photos = RestaurantPhotos::where('restaurant_id', $this->id)->get();
        $restaurant_photos = [];

        foreach($photos as $key => $value){
            $photo = new \stdClass();

            $photo->id = $value->id;
            $photo->filename = $value->filename;
            $photo->url = $value->photo_url;
            $photo->uploaded = $value->created_at;

            array_push($restaurant_photos, $photo);
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'city_id' => $this->city_id,
            'phone' => $this->phone,
            'owner_id' => $this->user_id,
            'open_days' => $this->open_days,
            'weekdays_time_open' => $this->weekdays_time_open,
            'weekdays_time_close' => $this->weekdays_time_close,
            'weekend_time_open' => $this->weekend_time_open,
            'weekend_time_close' => $this->weekend_time_close,
            'halal' => $this->halal,
            'open' => $this->open,
            'cuisine' => $cuisine_array,
            //'cuisine_zh' => $cuisine_zh,
            'type' => $type_array,
            //'type_zh' => $type_zh,
            'verified' => $this->verified,
            'rating' => number_format($rating, 1),
            'photo' => $restaurant_photos
        ];

    }

}