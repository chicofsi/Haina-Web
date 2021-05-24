<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationCategory extends Model
{
    use HasFactory;
    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */

    protected $table = 'notification_category';

    protected $fillable = [
    	'name', 'img', 'notification_for'
    ];

    public function usernotification(){
    	return $this->hasMany('App\Models\UserNotification','id_category','id');
    }
}
