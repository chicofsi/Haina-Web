<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocsCategory extends Model
{
    use HasFactory;
    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */

    protected $table = 'docs_category';

    protected $fillable = [
    	'name', 'icon_url'
    ];

    public function userdocs(){
    	return $this->hasMany('App\Models\UserDocs','id_docs_category','id');
    }
}
