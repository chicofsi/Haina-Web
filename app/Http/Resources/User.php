<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;
use App\Models\Education;
use App\Models\UserEducation;
use App\Models\UserWorkExperience;
use App\Models\UserNotification;

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

        if($education){
            $education_level = Education::where('id', $education['id_edu'])->first();
            $education->education_level = $education_level['name'];
        }
        
        $latest_work = UserWorkExperience::where('id_user', $this->id)->first();

        if($latest_work && $latest_work['date_end'] == null){
            $latest_work['date_end'] = "now";
        }

        $photo_url=URL::to('storage/'.$this->photo);

        $count = UserNotification::where('id_user',$this->id)->where('opened',0)->count();
        return [
            'id' => $this->id,
            'fullname' => $this->fullname,
            'notification_count' => $count,
            'email' => $this->email,
            'phone' => $this->phone,
            'username' => $this->username,
            'address' => $this->address,
            'birthdate' => $this->birthdate,
            'gender' => $this->gender,
            'about' => $this->about,
            'photo' => $photo_url,
            'education' => $education_level['name'] ?? null,
            'education_detail' => $education,
            'latest_work' => $latest_work
        ];
    }
}
