<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'fullname', 'email' ,'phone','username', 'password', 'address', 'birthdate', 'gender', 'about', 'firebase_uid',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'firebase_uid'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    /**protected $appends = [
        'photo',
    ];**/

    public function skill()
    {
        return $this->belongsToMany('App\Models\JobSkill', 'user_skills'); 
    }

    public function languages(){
        return $this->belongsToMany('App\Models\Languages', 'user_languages', 'id_language', 'id_user');
    }

    public function bookmark(){
        return $this->belongsToMany('App\Models\JobVacancy', 'job_bookmark', 'id_user', 'id_job_vacancy');
    }

    public function forum_bookmark(){
        return $this->belongsToMany('App\Models\ForumPost', 'forum_bookmark', 'user_id', 'post_id');
    }

    public function education(){
        return $this->hasOne('App\Models\UserEducation', 'id_user', 'id'); 
    }

    public function work_experience(){
        return $this->hasOne('App\Models\UserWorkExperience', 'id_user', 'id'); 
    }
}
