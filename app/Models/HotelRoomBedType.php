<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotelRoomBedType extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'hotel_room_bed';

    protected $fillable = [ 
        'bed_type'
    ];
    
    /*
    public function user(){
        return $this->belongsTo('App\Models\User','id_user','id');
    } 
    */
}
