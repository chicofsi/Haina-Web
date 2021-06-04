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

        if(isset($this->image)){
            $image = $this->image;
        }
        else{
            $image = "http://static.everypixel.com/ep-pixabay/0741/1093/6899/08857/7411093689908857422-news.jpg";
        }

        if(isset($this->categories)){
            $category = $this->categories[0]->label;
        }
        else{
            $category = "news/General";
        }

        return[
            'title' => $this->title,
            'date' => $this->date,
            'time' => $this->time,
            'body' => $this->body,
            'image' => $image,
            'category' => $category,
            'source' => $this->url,
             
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
