<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotelDarmaFacilitiesList extends Model
{
    use HasFactory;

    protected $table = 'hotel_darma_facilities_list';

    public $timestamps = false;

    protected $fillable = [ 
        'name'
    ];

    public function hotel(){
        return $this->belongsToMany(HotelDarma::class, 'hotel_darma_facilities', 'facilities_id', 'hotel_id');
    }

}