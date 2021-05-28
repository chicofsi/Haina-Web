<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EspayRequest extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'espay_request';

    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [
        'order_id', 'uuid', 'request', 'response','response_code','url','error_code'
    ];

}
