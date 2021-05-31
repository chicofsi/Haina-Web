<?php

namespace App\Http\Resources;

use App\Models\City;
use App\Models\HotelRoom;
use App\Models\HotelRating;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\FacilitiesResource;
use App\Http\Resources\HotelRoomResource;

class HotelResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $list_facilities=[];
        foreach($this->facilities as $key => $value){
            $list_facilities[$key] = new FacilitiesResource($value);
        }
        
        $hotel_city = City::select('name')->where('id', $this->city_id)->get();

        /*
        $hotel_image=[];
        foreach($this->image as $key => $value){
            $hotel_image[$key] = new HotelImageResource($value);
        }
        */

        $hotel_rooms=[];
        foreach($this->room as $key => $value){
            $hotel_rooms[$key] = new HotelRoomResource($value);
        }

        $starting_price = HotelRoom::selectRaw('min(room_price) as min_price')->where('hotel_id', $this->id)->first();

        $all_rating = HotelRating::selectRaw('avg(rating) as average')->where('hotel_id', $this->id)->first();
        

        return [
            'id' => $this->id,
            'hotel_name' => $this->hotel_name,
            'hotel_address' => $this->hotel_address,
            'hotel_phone' => $this->hotel_phone,
            'city_id' => $this->city_id,
            'hotel_city' => $hotel_city[0]['name'], 
            'hotel_long' => $this->hotel_long,
            'hotel_lat' =>$this->hotel_lat,
            'hotel_image' => $this->hotel_image,
            'facilities' =>$list_facilities,
            'rooms' => $hotel_rooms,
            'starting_price' => $starting_price['min_price'],
            'avg_rating' => $all_rating['average']
        ];
    }
}
