<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForumComment extends Model
{
    use HasFactory;

    protected $table = 'forum_comment';

    protected $fillable = [
    	 'user_id', 'post_id', 'content', 'id_reply'
    ];

    public function posts(){
        return $this->belongsTo('App\Models\ForumPost','post_id','id');
    }

    public function user(){
        return $this->belongsTo('App\Models\User','user_id','id');
    }

}
