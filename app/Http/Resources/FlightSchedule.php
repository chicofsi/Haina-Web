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
        $flightdetail=[];
        $flightTime=[];

        $airline=Airlines::where('airline_code',$this->airlineID)->first();
        foreach ($this->segment as $key => $value) {
            foreach($value->flightDetail as $k=>$v){
                array_push($flightdetail,$v);
                array_push($flightTime,[
                    "origin" => $v->fdOrigin,
                    "destination" => $v->fdDestination,
                    "depart_time"=>$v->fdDepartTime,
                    "arrival_time"=>$v->fdArrivalTime
                ]);
            }

        }

        return [
            'airline_code' => $this->airlineID,
            'airline_detail' => $airline,
            'depart_time' => $this->jiDepartTime,
            'arrival_time' => $this->jiArrivalTime,
            'origin' => $this->jiOrigin,
            'destination' => $this->jiDestination,
            'flight_detail' => $flightdetail,
            'flight_time' => $flightTime,
            'price' => $this->sumPrice,
            'journey_references' => $this->journeyReference 
        ];
    }
}
