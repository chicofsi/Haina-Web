<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hotel extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'hotel';

    public $timestamps = false;

    protected $fillable = [ 
        'hotel_name', 'hotel_address', 'hotel_phone', 'city_id', 'hotel_long', 'hotel_lat' 
    ];

    public function city(){
        return $this->hasOne('App\Models\City', 'id', 'city_id');
    }

    public function room(){
        return $this->hasMany('App\Models\HotelRoom', 'hotel_id');
    }

    public function image(){
        return $this->hasMany('App\Models\HotelImage', 'hotel_id');
    }

    public function facilities(){
        return $this->belongsToMany(Facilities::class, 'hotel_facilities', 'hotel_id', 'facilities_id');
    }
    
    /*
    
    //public function jobvacancy(){
    	return $this->hasMany('App\Models\JobVacancy','id_company','id');
    }
    public function address(){
        return $this->hasMany('App\Models\CompanyAddress','id_company','id');
    }
    public function photo(){
        return $this->hasMany('App\Models\CompanyPhoto','id_company','id');
    }
    public function user(){
        return $this->belongsTo('App\Models\User','id_user','id');
    } 
    */
}
