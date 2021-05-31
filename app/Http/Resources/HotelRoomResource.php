<?php

namespace App\Http\Resources;

use App\Models\HotelRoomBedType;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\HotelRoomImageResource;

class HotelRoomResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {

        $room_images=[];
        foreach($this->roomImage as $key => $value){
            $room_images[$key] = new HotelRoomImageResource($value);
        }

        $room_bed = HotelRoomBedType::find($this->room_bed_id);
        
        return [
            'id' => $this->id,
            'hotel_id' => $this->hotel_id,
            'room_name' => $this->room_name,
            'room_bed_id' => $this->room_bed_id,
            'room_bed_type' => $room_bed->bed_type,
            'room_price' => $this->room_price,
            'room_maxguest' =>$this->room_maxguest,
            'room_total' => $this->room_total,
            'room_image' => $room_images
        ];
    }
}
