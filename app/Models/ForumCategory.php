<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForumCategory extends Model
{
    use HasFactory;

    protected $table = 'forum_category';

    protected $fillable = [
    	 'name', 'name_zh', 'icon'
    ];
    
    public function subforum(){
        return $this->hasMany('App\Models\Subforum','category_id','id');
    }

}
