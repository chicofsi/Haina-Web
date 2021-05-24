<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;
use App\Models\City;
use App\Models\User;
use App\Models\UserDocs;
use App\Http\Resources\UserDocs as UserDocsResource;


class JobApplicant extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $user=User::where('id',$this->id_user)->first();
        $userDocs=UserDocs::where('id',$this->id_user_docs)->first();
        $userDocsData=new UserDocsResource($userDocs);

        
        $photo_url=URL::to('storage/'.$user->photo);
        
        $data=[
            'id'=>$this->id,
            'fullname' => $user->fullname,
            'username' => $user->username,
            'gender' => $user->gender,
            'photo' => $photo_url,
            'about' => $user->about,
            'address' => $user->address,
            'phone' => $user->phone,
            'email' => $user->email,
            'status'=>$this->status,
            'date'=>date_format($this->created_at,'Y-m-d'),
            'time'=>date_format($this->created_at,'H:i:s'),
            'user_document' => $userDocsData,
            'job_vacancy_title' => $this->jobvacancy->title,
        ];
        
        return $data;
    }
}
