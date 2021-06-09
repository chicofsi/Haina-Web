<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonalAccessToken extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'personal_access_tokens';

    protected $fillable = [
        'tokenable_type', 'tokenable_id', 'name', 'token', 'abilities'
    ];


}
