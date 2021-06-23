<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HotelDarmaCommonFacility extends Model
{
    use HasFactory;

    protected $table = 'common_facilities';

    public $timestamps = false;

    protected $fillable = [ 
        'name', 'icon'
    ];

}