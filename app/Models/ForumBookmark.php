<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForumBookmark extends Model
{
    use HasFactory;

    protected $table = 'forum_bookmark';

    protected $fillable = [
    	'user_id', 'post_id'
    ];


}
