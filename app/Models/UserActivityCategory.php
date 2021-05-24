<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserActivityCategory extends Model
{
    use HasFactory;
    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */

    protected $table = 'user_activity_category';

    protected $fillable = [
    	'name'
    ];

    public function useractivity(){
    	return $this->hasMany('App\Models\UserActivity','id_user_activity_category','id');
    }
}
