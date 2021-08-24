<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobVacancyApplicant extends Model
{
    use HasFactory;

    protected $table = 'job_vacancy_applicant';

    protected $fillable = [
    	'id_vacancy', 'id_user', 'status', 'applicant_notes'
    ];

    public function vacancy(){
    	return $this->belongsTo('App\Models\JobVacancy','id_job_vacancy','id');
    } 
    public function user(){
    	return $this->belongsTo('App\Models\User','id_user','id');
    }

}