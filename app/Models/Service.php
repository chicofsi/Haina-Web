<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'service';

    protected $fillable = [
        'service_name', 'active',
    ];



    public function service_admin(){
    	return $this->hasMany('App\Models\ServiceAdmin','id','id_service');
    }
    public function subservice(){
    	return $this->hasMany('App\Models\SubService','id','id_service');
    }

    public function mitra_user(){
    	return $this->hasMany('App\Models\MitraUser','id','id_service');
    }
}
