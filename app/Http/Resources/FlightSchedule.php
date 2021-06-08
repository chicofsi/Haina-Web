<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;


use App\Models\Airlines;
class FlightSchedule extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $airline=Airlines::where('airline_code',$this->airlineID)->first();
        foreach ($this->segment as $key => $value) {
            $flightdetail[$key]=$value;
        }

        return [
            'airline_code' => $this->airlineID,
            'airline_detail' => $airline,
            'depart_time' => $this->jiDepartTime,
            'arrival_time' => $this->jiArrivalTime,
            'origin' => $this->jiOrigin,
            'destination' => $this->jiDestination,
            'flight_detail' => $flightdetail,
            'price' => $this->sumPrice,
            'journey_references' => $this->journeyReference
        ];
    }
}
