<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'news';

    protected $fillable = [
        'title', 'url', 'photo_url', 'source', 'id_category', 'created_by', 'language'
    ];



    public function creator(){
    	return $this->belongsTo('App\Models\Admin','id','created_by');
    }
    public function category(){
        return $this->belongsTo('App\Models\NewsCategory','id_category','id');
    }
}
