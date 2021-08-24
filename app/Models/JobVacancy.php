<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobVacancy extends Model
{
    use HasFactory;

    protected $table = 'job_vacancy';

    protected $fillable = [
    	 'id_company', 'position', 'type', 'level', 'experience', 'id_specialist', 'id_city', 'address', 
         'min_salary', 'max_salary', 'salary_display', 'id_edu', 'description', 'package', 'deleted_at'
    ];

    public function company(){
        return $this->belongsTo('App\Models\Company','id_company','id');
    }

    public function skill(){
        return $this->belongsToMany('App\Models\JobSkill', 'job_vacancy_skills', 'id_vacancy', 'id_skill');
    }

    public function applicant(){
        return $this->hasMany('App\Models\JobVacancyApplicant','id_vacancy','id');
    }

    public function payment(){
        return $this->hasOne('App\Models\JobVacancyPayment', 'id_vacancy', 'id');
    }

    public function specialist(){
        return $this->hasOne('App\Models\JobCategory', 'id', 'id_specialist');
    }

    public function education(){
        return $this->hasOne('App\Models\Education', 'id', 'id_edu');
    }

    public function city(){
        return $this->hasOne('App\Models\City', 'id', 'id_city');
    }

}