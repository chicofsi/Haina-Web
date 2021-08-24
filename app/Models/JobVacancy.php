<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobVacancy extends Model
{
    use HasFactory;

    protected $table = 'job_vacancy';

    protected $fillable = [
    	 'id_company', 'position', 'type', 'level', 'experience', 'id_specialist', 'id_city', 'address', 'min_salary', 'max_salary', 'id_edu', 'description', 'package'
    ];

    public function company(){
        return $this->belongsTo('App\Models\Company','id_company','id');
    }

    public function skill(){
        return $this->belongsToMany('App\Models\JobSkill', 'job_vacancy_skills', 'id_skill', 'id_vacancy');
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

}