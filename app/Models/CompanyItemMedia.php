<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyItemMedia extends Model
{

    use HasFactory;

    protected $table = 'company_item_media';

    
    protected $fillable = [
        'id_item', 'media_url'
    ];


}