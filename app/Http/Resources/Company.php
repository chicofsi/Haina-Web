<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;
use App\Http\Resources\CompanyAddress as CompanyAddressResource;
use App\Http\Resources\CompanyMedia as CompanyMediaResource;
use App\Models\Province;
use App\Models\CompanyCategory;

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
        $primary_address = null;
        foreach ($this->address as $key => $value) {
            $address[$key]=new CompanyAddressResource($value);
            if($value->primary_address == 1){
                $primary_address = new CompanyAddressResource($value);
            }
        }

        if($primary_address == null && $this->address != []){
            $primary_address = $address[0];
        }

        $photo=[];
        foreach ($this->photo as $key => $value) {
            $photo[$key]=new CompanyMediaResource($value);
        }
        $icon=URL::to('storage/'.$this->icon_url);

        $province = Province::where('id', $this->id_province)->first();

        $category = $this->category;
        foreach($category as $key => $value){
            unset($value->created_at);
            unset($value->updated_at);
            unset($value->pivot);
        }

        return [
            'id'=>$this->id,
            'name'=>$this->name,
            'description'=>$this->description,
            'status'=>$this->status,
            'icon_url'=>$icon,
            'year' => $this->year,
            'staff_size' => $this->staff_size,
            'siup' => $this->siup,
            'contact_number' => $this->contact_number,
            'id_province' => $this->id_province,
            'province' => $province['name'],
            'primary_address' => $primary_address,
            'address'=>$address,
            'category'=>$category,
            'media'=>$photo,
        ];
    }
}
