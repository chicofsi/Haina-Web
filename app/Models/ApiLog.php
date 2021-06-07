<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiLog extends Model
{
    use HasFactory;

    protected $table = 'api_log';

    protected $fillable = [
    	'ip_address', 'id_user', 'method', 'request', 'response', 'response_code','url'
    ];
}
