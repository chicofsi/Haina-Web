<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotelDarmaImage extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'hotel_darma_images';

    public $timestamps = false;

    protected $fillable = [ 
        'hotel_id', 'image'
    ];

    public function hotel(){
        return $this->belongsTo('App\Models\HotelDarma', 'id', 'hotel_id');
    }

}
