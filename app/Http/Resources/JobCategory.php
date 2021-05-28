<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;

class JobCategory extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $photo=URL::to('storage/'.$this->photo_url);
        return [
            'id'=>$this->id,
            'name'=>$this->name,
            'name_zh'=>$this->name_zh,
            'display_name'=>$this->display_name,
            'photo_url'=>$photo
        ];
    }
}
