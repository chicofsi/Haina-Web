<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'city';

    protected $fillable = [
    	'name', 'id_province'
    ]; 

    public function province(){
    	return $this->hasMany('App\Models\Province','id_province','id');
    }

}
