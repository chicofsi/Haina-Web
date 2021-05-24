<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;
use App\Http\Resources\CompanyAddress as CompanyAddressResource;
use App\Http\Resources\CompanyPhoto as CompanyPhotoResource;

class Company extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $address=[];
        foreach ($this->address as $key => $value) {
            $address[$key]=new CompanyAddressResource($value);
        }

        $photo=[];
        foreach ($this->photo as $key => $value) {
            $photo[$key]=new CompanyPhotoResource($value);
        }
        $icon=URL::to('storage/'.$this->icon_url);

        return [
            'id'=>$this->id,
            'name'=>$this->name,
            'description'=>$this->description,
            'status'=>$this->status,
            'icon_url'=>$icon,
            'address'=>$address,
            'photo'=>$photo,
        ];
    }
}
