<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DarmawisataRequest extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'darmawisata_request';

    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [
         'request', 'response','response_code','url', 'status'
    ];

}
