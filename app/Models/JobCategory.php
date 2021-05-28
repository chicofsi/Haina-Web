<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobCategory extends Model
{
    use HasFactory;
    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */

    protected $table = 'job_category';

    protected $fillable = [
    	'name', 'name_zh', 'display_name', 'photo_url'
    ];

    public function jobvacancy(){
    	return $this->hasMany('App\Models\JobVacancy','id_category','id');
    }
}
