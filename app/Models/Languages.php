<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Languages extends Model
{
    use HasFactory;

    protected $table = 'languages';

    protected $fillable = ['name'];

    public function user(){
        return $this->belongsToMany('App\Models\User', 'user_languages', 'id_user', 'id_language');
    }

}