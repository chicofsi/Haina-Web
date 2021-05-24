<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotelBooking extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'hotel_booking';

    protected $fillable = [ 
        'hotel_id', 'room_id', 'user_id', 'check_in', 'check_out', 'total_night', 'total_guest', 'total_price', 'status', 'order_id' 
    ];

    public function hotel(){
        return $this->belongsTo('App\Models\Hotel', 'hotel_id', 'id');
    }

    public function room(){
        return $this->belongsTo('App\Models\HotelRoom', 'room_id', 'id');
    }

    public function users(){
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }

    public function payment(){
        return $this->hasOne('App\Models\HotelBookingPayment', 'booking_id');
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
