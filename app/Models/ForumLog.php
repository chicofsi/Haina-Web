<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForumLog extends Model
{
    use HasFactory;

    protected $table = 'forum_log';

    protected $fillable = [
    	 'user_id', 'subforum_id', 'forum_action', 'message'
    ];

    public function subforum(){
        return $this->belongsTo('App\Models\Subforum','id', 'subforum_id');
    } 

    public function user(){
        return $this->belongsTo('App\Models\User','user_id','id');
    }


}