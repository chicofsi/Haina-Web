<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSuspend extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'user_suspend';

    protected $fillable = [
        'id_user', 'suspended_by', 'message', 'img'
    ];



    public function notificationcategory(){
    	return $this->belongsTo('App\Models\NotificationCategory','id_category','id');
    } 
    public function user(){
        return $this->belongsTo('App\Models\User','id_user','id');
    }
}
