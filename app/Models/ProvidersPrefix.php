<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProvidersPrefix extends Model
{
    use HasFactory;

    protected $table = 'providers_prefix';

    protected $primaryKey = null;
    public $incrementing = false;
    
    protected $fillable = [
    	 'id_providers', 'prefix'
    ];


    public function providers(){
    	return $this->belongsTo('App\Models\Providers','id_providers','id');
    } 
    
}
