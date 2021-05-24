<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminActivityCategory extends Model
{
    use HasFactory;
    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */

    protected $table = 'admin_activity_category';

    protected $fillable = [
    	'name'
    ];

    public function adminactivity(){
    	return $this->hasMany('App\Models\AdminActivity','id_admin_activity_category','id');
    }
}
