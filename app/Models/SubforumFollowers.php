<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubforumFollowers extends Model
{
    use HasFactory;

    protected $table = 'forum_subforum_followers';

    protected $fillable = [
    	 'subforum_id', 'user_id'
    ];

    public $timestamps = false;

}
