<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\HotelResource;
use App\Models\City;
use App\Models\HotelRoom;
use App\Http\Resources\FacilitiesResource;
use App\Http\Resources\HotelImageResource;
use App\Http\Resources\HotelRoomResource;

class HotelDetailResource extends JsonResource
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
        
        $hotel_image=[];
        foreach($this->image as $key => $value){
            $hotel_image[$key] = new HotelImageResource($value);
        }

        $hotel_rooms=[];
        foreach($this->room as $key => $value){
            $hotel_rooms[$key] = new HotelRoomResource($value);
        }

        $hotel_city = City::select('name')->where('id', $this->city_id)->get();

        $starting_price = HotelRoom::selectRaw('min(room_price) as min_price')->where('hotel_id', $this->id)->first();

        return [
            'id' => $this->id,
            'hotel_name' => $this->hotel_name,
            'hotel_address' => $this->hotel_address,
            'hotel_phone' => $this->hotel_phone,
            'city_id' => $this->city_id,
            'hotel_city' => $hotel_city[0]['name'],
            'hotel_long' => $this->hotel_long,
            'hotel_lat' =>$this->hotel_lat,
            'facilities' =>$list_facilities,
            'image' => $hotel_image,
            'rooms' => $hotel_rooms,
            'min' => $starting_price['min_price']
        ];
    }
}
