<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLogs extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'user_logs';

    protected $fillable = [
        'id_user', 'id_user_activity', 'message'
    ];



    public function useractivity(){
    	return $this->belongsTo('App\Models\UserActivity','id_user_activity','id');
    } 
    public function user(){
        return $this->belongsTo('App\Models\User','id_user','id');
    } 
}
