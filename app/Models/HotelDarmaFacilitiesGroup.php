<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotelDarmaFacilitiesGroup extends Model
{
    use HasFactory;

    protected $table = 'hotel_darma_facility_group';

    public $timestamps = false;

    protected $fillable = [ 
        'name', 'icon'
    ];

}