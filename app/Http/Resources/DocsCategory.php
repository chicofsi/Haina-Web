<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;

class DocsCategory extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $photo=URL::to('storage/'.$this->icon_url);
        return [
            'id'=>$this->id,
            'name'=>$this->name,
            'icon_url'=>$photo
        ];
    }
}
