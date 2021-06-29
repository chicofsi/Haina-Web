<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyImageData extends Model
{

    use HasFactory;

    protected $table = 'property_image_data_master';

    protected $fillable = [
    	 'id_property', 'filename', 'path'
    ];

    public function property(){
    	return $this->belongsTo('App\Models\PropertyData','id_property','id');
    } 

}