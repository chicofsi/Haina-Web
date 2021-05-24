<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialMedia extends Model
{
    use HasFactory;
    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */

    protected $table = 'social_media';

    protected $fillable = [
    	'name', 'icon_url'
    ];

    public function usersocialmedia(){
    	return $this->hasMany('App\Models\UserSocialMedia','id_social_media','id');
    }
}
