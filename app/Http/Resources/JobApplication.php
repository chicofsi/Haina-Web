<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;
use App\Models\City;
use App\Models\Company;
use App\Models\CompanyAddress;
use App\Models\JobCategory;
use App\Http\Resources\Company as CompanyResource;
use App\Http\Resources\CompanyAddress as CompanyAddressResource;
use App\Http\Resources\UserDocs as UserDocsResource;


class JobApplication extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        
        $company=Company::where('id',$this->jobvacancy->id_company)->first();
        $companyaddress=CompanyAddress::where('id',$this->jobvacancy->id_address)->first();
        $jobcategory=JobCategory::where('id',$this->jobvacancy->id_category)->first();
        $jobphoto=URL::to('storage/'.$this->jobvacancy->photo_url);
        
        $photo_url=URL::to('storage/'.$this->user->photo);

        $companydata=[
            "id"=>$company->id,
            "name"=>$company->name,
            "description"=>$company->description,

            "icon_url"=>URL::to('storage/'.$company->icon_url)
        ];
        
        $data=[
            'id'=>$this->id,
            'jobtitle'=>$this->jobvacancy->title,
            'jobdescription'=>$this->jobvacancy->description,
            'salary_from'=>$this->jobvacancy->salary_from,
            'salary_to'=>$this->jobvacancy->salary_to,
            'jobpicture'=>$jobphoto,
            'jobaddress'=>new CompanyAddressResource($companyaddress),
            'company'=>$companydata,
            'userdocs'=>new UserDocsResource($this->userdocs),

            'status'=>$this->status,
            'date'=>date_format($this->created_at,'Y-m-d'),
            'time'=>date_format($this->created_at,'H:i:s'),
        ];
        
        return $data;
    }
}
