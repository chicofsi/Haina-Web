<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForumPost extends Model
{
    use HasFactory;

    protected $table = 'forum_post';

    protected $fillable = [
    	 'user_id', 'subforum_id', 'title', 'content', 'view_count', 'share_count'
    ];

    public function subforum(){
        return $this->belongsTo('App\Models\Subforum','id', 'subforum_id');
    } 

    public function user(){
        return $this->belongsTo('App\Models\User','user_id','id');
    }
    
    public function comments(){
        return $this->hasMany('App\Models\ForumComment','post_id','id');
    }

    public function images(){
        return $this->hasMany('App\Models\ForumImage','post_id','id');
    }

    public function videos(){
        return $this->hasMany('App\Models\ForumVideo','post_id','id');
    }

}
