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
            if($value->length()>1){
                foreach($value as $k=>$v){
                    $flightdetail[$k]=$v;
                    $flightTime[$k]=[
                        "origin" => $v->fdOrigin,
                        "destination" => $v->fdDestination,
                        "depart_time"=>$v->fdDepartTime,
                        "arrival_time"=>$v->fdArrivalTime
                    ];
                }
            }else{
                $flightdetail[$key]=$value;
                $flightTime[$key]=[
                    "origin" => $value->flightDetail[0]->fdOrigin,
                    "destination" => $value->flightDetail[0]->fdDestination,
                    "depart_time"=>$value->flightDetail[0]->fdDepartTime,
                    "arrival_time"=>$value->flightDetail[0]->fdArrivalTime
                ];
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
