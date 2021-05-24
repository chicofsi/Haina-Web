<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDocs extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'user_docs';

    protected $fillable = [
        'id_user', 'id_docs_category', 'docs_name', 'docs_url'
    ];



    public function user(){
    	return $this->belongsTo('App\Models\User','id_user','id');
    } 
    public function docscategory(){
        return $this->belongsTo('App\Models\DocsCategory','id_docs_category','id');
    } 
}
