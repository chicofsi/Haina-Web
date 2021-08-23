<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForumMod extends Model
{
    use HasFactory;

    protected $table = 'forum_mod';

    protected $fillable = [
    	 'user_id', 'role', 'subforum_id'
    ];

    public function subforum(){
        return $this->belongsTo('App\Models\Subforum','id', 'subforum_id');
    } 

    public function user(){
        return $this->belongsTo('App\Models\User','user_id','id');
    } 

}