<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;

class CompanyItemMedia extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $photo=URL::to('storage/'.$this->media_url);

        return [
            'id'=>$this->id,
            'media_url'=>$photo,
            'media_type'=>$this->media_type
        ];
    }
}
