<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyData extends Model
{
    use HasFactory;

    protected $table = 'property_data_master';

    protected $fillable = [
    	 'id_user', 'property_type', 'title', 'condition', 'building_area', 'land_area', 'bedroom', 'bathroom', 
         'floor_level', 'year', 'certificate_type', 'id_city', 'address', 'latitude', 'longitude', 
         'selling_price', 'rental_price', 'facilities', 'post_date', 'description', 'status', 'expiry_date', 'views'
    ];

    //
    public function owner(){
    	return $this->belongsTo('App\Models\User','id_user','id');
    }

    public function city(){
        return $this->belongsTo('App\Models\City','id_city','id');
    }

    public function images(){
        return $this->hasMany('App\Models\PropertyImageData','id_property','id');
    }
    //
}
