<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobVacancyPackage extends Model
{
    use HasFactory;

    protected $table = 'job_vacancy_package';

    protected $fillable = [
        'name', 'price', 'description', 'description_zh'
    ];

}