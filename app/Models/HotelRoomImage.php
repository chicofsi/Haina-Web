<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotelRoomImage extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'hotel_room_img';

    protected $fillable = [ 
        'room_id', 'url'
    ];

    public function hotel(){
        return $this->belongsTo('App\Models\HotelRoom', 'id', 'room_id');
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
