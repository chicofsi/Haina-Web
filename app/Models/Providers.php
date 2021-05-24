<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Providers extends Model
{
    use HasFactory;

    protected $table = 'providers';

    protected $fillable = [
    	 'name', 'photo_url'
    ];


    
    public function prefix(){
        return $this->hasMany('App\Models\ProvidersPrefix','id_providers','id');
    } 
}
