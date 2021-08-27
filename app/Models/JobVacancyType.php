<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobVacancyType extends Model
{
    use HasFactory;

    protected $table = 'job_vacancy_type';

    protected $fillable = [
        'name'
    ];

}