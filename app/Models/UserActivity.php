<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserActivity extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'user_activity';

    protected $fillable = [
        'id_user_activity_category', 'name', 'default_message'
    ];



    public function useractivitycategory(){
    	return $this->belongsTo('App\Models\UserActivityCategory','id_user_activity_category','id');
    } 
    public function userlogs(){
        return $this->hasMany('App\Models\UserLogs','id_user_activity','id');
    }
}
