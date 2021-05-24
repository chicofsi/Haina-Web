<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;
use App\Models\City;
use App\Http\Resources\JobApplicant as JobApplicantResource;

class JobVacancy extends JsonResource
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
        $icon=URL::to('storage/'.$this->company->icon_url);
        $location=City::where('id',$this->address->id_city)->first();
        $creator=[
            'id'=>$this->company->id,
            'name'=>$this->company->name,
            'description'=>$this->company->description,
            'icon_url'=>$icon,
        ];
        
        $data=[
            'id'=>$this->id,
            'title'=>$this->title,
            'status'=>$this->status,
            'jobcategory'=>$this->category->name,
            'location'=>$location->name,
            'salary_from'=>$this->salary_from,
            'salary_to'=>$this->salary_to,
            'description'=>$this->description,
            'photo_url'=>$photo,
            'company'=>$creator,
            'date'=>date_format($this->created_at,'Y-m-d'),
            'time'=>date_format($this->created_at,'H:i:s'),
            'jobapplicant' => $this->when($this->has('jobapplicant'), function () {
                $applicant=[];
                foreach ($this->jobapplicant as $key => $value) {
                    $applicant[$key]=new JobApplicantResource($value);
                }
                return $applicant;
            }),
            'skill' => $this->when($this->has('skill'), function () {
                $skill=[];
                foreach ($this->skill as $key => $value) {
                    $skill[$key]=['name'=>$value->name];
                }
                return $skill;
            }),
            'address' => $this->address
        ];
        
        return $data;
    }
}
