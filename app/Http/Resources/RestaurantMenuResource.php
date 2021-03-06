<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;

use App\Models\RestaurantData;
use App\Models\RestaurantMenu;
use App\Models\RestaurantMenuPhotos;

use App\Http\Resources\ProductResource;

class RestaurantMenuResource extends JsonResource {

    public function toArray($request){

        $restaurant_name = RestaurantData::select('name')->where('id', $this->restaurant_id)->first();

        $menu_photos = RestaurantMenuPhotos::where('menu_id', $this->id)->get();

        $menu = [];

        foreach($menu_photos as $key => $value){
            $photo = new \stdClass();

            $photo->id = $value->id;
            $photo->filename = $value->filename;
            $photo->url = $value->photo_url;
            $photo->uploaded = $value->created_at;

            array_push($menu, $photo);
        }

        return [
            'id' => $this->id,
            'restaurant_id' => $this->restaurant_id,
            'restaurant_name' => $restaurant_name['name'],
            'menu_name' => $this->menu_name,
            'menu_images' => $menu
        ];
    }

}