<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserEducation extends Model
{
    use HasFactory;

    protected $table = 'user_education_detail';

    protected $fillable = [
        'id_user', 'institution', 'year_start', 'year_end', 'gpa', 'major',
        'id_edu', 'city'
    ];

    public function user(){
    	return $this->belongsTo('App\Models\User','id_user','id');
    }

    public function education(){
        return $this->hasOne('App\Models\Education', 'id', 'id_edu');
    }

}