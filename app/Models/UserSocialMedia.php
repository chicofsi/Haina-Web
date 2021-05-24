<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSocialMedia extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'user_social_media';

    protected $fillable = [
        'id_social_media','id_user', 'link'
    ];



    public function socialmedia(){
    	return $this->belongsTo('App\Models\SocialMedia','id_social_media','id');
    } 
    public function user(){
        return $this->belongsTo('App\Models\User','id_user','id');
    }
}
