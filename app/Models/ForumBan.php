<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForumBan extends Model
{
    use HasFactory;

    protected $table = 'forum_banlist';

    protected $fillable = [
    	 'user_id', 'subforum_id', 'mod_id', 'reason'
    ];

    public function subforum(){
        return $this->belongsTo('App\Models\Subforum','id', 'subforum_id');
    } 

    public function user(){
        return $this->belongsTo('App\Models\User','user_id','id');
    }

    public function mod(){
        return $this->belongsTo('App\Models\ForumMod','mod_id','id');
    }

}