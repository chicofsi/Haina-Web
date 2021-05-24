<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsCategory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'news_category';

    protected $fillable = [
        'name'
    ];



    public function news(){
    	return $this->hasMany('App\Models\News','id_category','id');
    }
}
