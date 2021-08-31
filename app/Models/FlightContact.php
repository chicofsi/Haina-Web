<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlightContact extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'flight_contact';

    protected $fillable = [
        'id_flight_book', 'title', 'first_name', 'last_name', 'country_code_phone',  'area_code_phone', 'remaining_phone_no'
    ];

    public $timestamps = false;

}
