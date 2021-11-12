<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;
use App\Http\Resources\CompanyAddress as CompanyAddressResource;
use App\Http\Resources\CompanyMedia as CompanyMediaResource;
use App\Models\Province;

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
            $photo[$key]=new CompanyMediaResource($value);
        }
        $icon=URL::to('storage/'.$this->icon_url);

        $province = Province::where('id', $this->id_province)->first();

        return [
            'id'=>$this->id,
            'name'=>$this->name,
            'description'=>$this->description,
            'status'=>$this->status,
            'icon_url'=>$icon,
            'year' => $this->year,
            'staff_size' => $this->staff_size,
            'siup' => $this->siup,
            'id_province' => $this->id_province,
            'province' => $province['name'],
            'address'=>$address,
            'media'=>$photo,
        ];
    }
}
