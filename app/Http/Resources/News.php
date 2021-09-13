<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class News extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {

        if(isset($this->photo_url)){
            $image = $this->photo_url;
        }
        else{
            $image = "http://static.everypixel.com/ep-pixabay/0741/1093/6899/08857/7411093689908857422-news.jpg";
        }


        return[
            'title' => $this->title,
            'image' => $image,
            'category' => $this->category,
            'source_name' => $this->source,
            'url' => $this->url,
             
        ];
        /*
        return [
            'id'=>$this->id,
            'title'=>$this->title,
            'url'=>$this->url,
            'photo_url'=>$this->photo_url,
            'category'=>$this->category->name,
        ];
        */
    }
}
