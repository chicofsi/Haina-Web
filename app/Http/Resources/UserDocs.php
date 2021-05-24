<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;
use App\Models\City;

class UserDocs extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $docs=URL::to('storage/'.$this->docs_url);
        return [
            'id'=>$this->id,
            'docs_name'=>$this->docs_name,
            'docs_url'=>$docs,
            'docs_category'=>$this->docscategory->name
        ];
    }
}
