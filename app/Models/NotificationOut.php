<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationOut extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'notification_out';

    protected $fillable = [
        'id_user', 'firebase_token', 'title','body','type','tabs'
    ];



}
