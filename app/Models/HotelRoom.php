<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotelRoom extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'hotel_rooms';

    public $timestamps = false;

    protected $fillable = [ 
        'hotel_id', 'room_name', 'room_bed_id', 'room_price', 'room_maxguest', 'room_total'
    ];

    public function hotel(){
        return $this->belongsTo('App\Models\Hotel', 'id', 'hotel_id');
    }

    public function bedType(){
        return $this->hasOne('App\Models\HotelRoomBedType', 'id', 'room_bed_id');
    }

    public function roomImage(){
        return $this->hasMany('App\Models\HotelRoomImage', 'room_id');
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
