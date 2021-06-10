<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserNotification extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'user_notification';

    protected $fillable = [
        'id_category','id_user', 'message'
    ];



    public function notificationcategory(){
    	return $this->belongsTo('App\Models\NotificationCategory','id_category','id');
    } 
    public function user(){
        return $this->belongsTo('App\Models\User','id_user','id');
    }
}
