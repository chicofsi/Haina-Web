<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobBookmark extends Model
{
    use HasFactory;

    protected $table = 'job_bookmark';

    protected $fillable = [
    	'id_job_vacancy', 'id_user'
    ];

    public function jobvacancy(){
    	return $this->belongsTo('App\Models\JobVacancy','id_job_vacancy','id');
    } 
    public function user(){
    	return $this->belongsTo('App\Models\User','id_user','id');
    } 
    
    
}
