<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;

class Post extends JsonResource
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
        $creatorData=[
            'id'=>$this->creator->id,
            'fullname'=>$this->creator->fullname,
            'username'=>$this->creator->username
        ];
        $subCategoryData=[
            'id'=>$this->subcategory->id,
            'name'=>$this->subcategory->name,
        ];
        return [
            'id'=>$this->id,
            'title'=>$this->title,
            'status'=>$this->status,
            'photo_url'=>$photo,
            'date'=>date_format($this->created_at,'Y-m-d'),
            'time'=>date_format($this->created_at,'H:i:s'),
            'creator'=>$creatorData,
            'subcategory'=>$subCategoryData,

        ];
    }
}
