<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubService extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'subservice';

    protected $fillable = [
        'id_service','name', 'active',
    ];

    

    public function service(){
    	return $this->belongsTo('App\Models\Service','id_service');
    }
}
