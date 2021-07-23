<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subforum extends Model
{
    use HasFactory;

    protected $table = 'forum_subforum';

    protected $fillable = [
    	 'name', 'description', 'category_id'
    ];

    public function posts(){
        return $this->hasMany('App\Models\ForumPost','subforum_id','id');
    } 

}
