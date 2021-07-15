<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForumImage extends Model
{
    use HasFactory;

    protected $table = 'forum_images';

    protected $fillable = [
    	 'user_id', 'post_id', 'filename', 'path'
    ];

    public function posts(){
        return $this->belongsTo('App\Models\ForumPost','post_id','id');
    } 

    public function user(){
        return $this->belongsTo('App\Models\User','user_id','id');
    } 

}