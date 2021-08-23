<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForumUpvote extends Model
{
    use HasFactory;

    protected $table = 'forum_upvote';

    protected $fillable = [
    	 'user_id', 'post_id', 'content'
    ];

    public function posts(){
        return $this->belongsTo('App\Models\ForumPost','id', 'post_id');
    } 

    public function user(){
        return $this->belongsTo('App\Models\User','user_id','id');
    } 

}
