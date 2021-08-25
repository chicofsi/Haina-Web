<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobVacancyLevel extends Model
{
    use HasFactory;

    protected $table = 'job_vacancy_level';

    protected $fillable = [
        'name'
    ];

}