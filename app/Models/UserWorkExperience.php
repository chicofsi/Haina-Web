<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobVacancy extends Model
{
    use HasFactory;

    protected $table = 'user_work_experience';

    protected $fillable = [
        'id_user', 'company', 'city', 'date_start', 'date_end', 'position', 'description', 'salary'
    ];

    public function user(){
    	return $this->belongsTo('App\Models\User','id_user','id');
    }

}