<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyFacility extends Model
{
    use HasFactory;

    protected $table = 'property_facility';

    protected $fillable = [
    	 'id', 'name'
    ];

}