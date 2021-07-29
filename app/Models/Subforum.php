<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subforum extends Model
{
    use HasFactory;

    protected $table = 'forum_subforum';

    protected $fillable = [
    	 'name', 'description', 'category_id', 'subforum_image', 'creator_id'
    ];

    public function posts(){
        return $this->hasMany('App\Models\ForumPost','subforum_id','id');
    } 

    public function creator(){
        return $this->belongsTo('App\Models\User','user_id','id');
    }

}
