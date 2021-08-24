<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobApplicantOld extends Model
{
    use HasFactory;

    protected $table = 'job_applicant';

    protected $fillable = [
    	'id_job_vacancy', 'id_user', 'id_user_docs', 'status'
    ];

    public function jobvacancy(){
    	return $this->belongsTo('App\Models\JobVacancy','id_job_vacancy','id');
    } 
    public function user(){
    	return $this->belongsTo('App\Models\User','id_user','id');
    } 
    public function userdocs(){
    	return $this->belongsTo('App\Models\UserDocs','id_user_docs','id');
    } 
    
}
