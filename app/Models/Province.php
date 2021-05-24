<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    use HasFactory;
    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */

    protected $table = 'province';

    protected $fillable = [
    	'name'
    ];

    public function city(){
    	return $this->hasMany('App\Models\City','id_province','id');
    }
}
