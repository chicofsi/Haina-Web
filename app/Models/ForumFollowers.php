<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForumFollowers extends Model
{
    use HasFactory;

    protected $table = 'forum_followers';

    protected $fillable = [
    	 'user_id', 'follower_id'
    ];

    public $timestamps = false;

}
