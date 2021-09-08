<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;
use App\Models\Education;
use App\Models\UserEducation;
use App\Models\UserWorkExperience;

class User extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $education = UserEducation::where('id_user', $this->id)->first();
        $education_level = Education::where('id', $education['id_edu'])->first();
        $latest_work = UserWorkExperience::where('id_user', $this->id)->orderBy('created_at', 'desc')->first();

        if($latest_work && $latest_work['date_end'] == null){
            $latest_work['date_end'] = "now";
        }

        $photo_url=URL::to('storage/'.$this->photo);
        return [
            'fullname' => $this->fullname,
            'email' => $this->email,
            'phone' => $this->phone,
            'username' => $this->username,
            'address' => $this->address,
            'birthdate' => $this->birthdate,
            'gender' => $this->gender,
            'about' => $this->about,
            'photo' => $photo_url,
            'education' => $education_level['name'],
            'education_detail' => $education,
            'latest_work' => $latest_work
        ];
    }
}
