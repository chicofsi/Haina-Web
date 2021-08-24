<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobVacancyInterview extends Model
{
    use HasFactory;

    protected $table = 'job_vacancy_interview';

    protected $fillable = [
    	 'id_user', 'id_vacancy', 'invitation', 'time', 'method', 'location', 'cp_name', 'cp_phone'
    ];

    public function vacancy(){
        return $this->belongsTo('App\Models\JobVacancy', 'id', 'id_vacancy');
    }

    public function user(){
    	return $this->belongsTo('App\Models\User','id_user','id');
    }

}