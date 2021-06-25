<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

use App\Models\HotelDarma;

class HotelDarmaBookingResource extends JsonResource
{

    public function toArray($request)
    {

        $hotel = HotelDarma::where('id', $this->hotel_id)->first();

        return [
            'id' => $this->id,
            'hotel_id' => $this->hotel_id,
            'hotel_name' => $hotel['hotel_name'],
            'room_id' => $this->room_id,
            'user_id' => $this->user_id,
            'check_in' => $this->check_in,
            'check_out' => $this->check_out,
            'total_price' => $this->total_price,
            'requests' => $this->requests,
            'status' => $this->status,
            'reservation_no' => $this->reservation_no,
            'agent_os_ref' => $this->agent_os_ref,
            'booking_date' => $this->booking_date,
            'cancelation_policy' => $this->cancelation_policy,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
