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

    }

    public function skill(){

    }

    public function applicant(){

    }

    public function payment(){

    }

}