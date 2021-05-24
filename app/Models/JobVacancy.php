<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobVacancy extends Model
{
    use HasFactory;

    protected $table = 'job_vacancy';

    protected $fillable = [
    	 'id_address', 'id_category', 'photo_url', 'title', 'status', 'description', 'salary_from', 'salary_to', 'id_company'
    ];


    public function address(){
    	return $this->belongsTo('App\Models\CompanyAddress','id_address','id');
    } 
    public function category(){
    	return $this->belongsTo('App\Models\JobCategory','id_category','id');
    } 
    public function company(){
        return $this->belongsTo('App\Models\Company','id_company','id');
    } 
    public function jobapplicant(){
        return $this->hasMany('App\Models\JobApplicant','id_job_vacancy','id');
    } 
    public function skill()
    {
        return $this->belongsToMany('App\Models\JobSkill', 'job_vacancy_skills');
    }
}
