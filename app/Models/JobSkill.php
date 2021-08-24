<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobSkill extends Model
{
    use HasFactory;
    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */

    protected $table = 'job_skill';

    protected $fillable = [
    	'name'
    ];

    public function jobvacancy()
    {
        return $this->belongsToMany('App\Models\JobVacancy', 'job_vacancy_skills', 'id_vacancy', 'id_skill');
    }
    public function user()
    {
        return $this->belongsToMany('App\Models\Users', 'user_skills'); 
    }
       
}
