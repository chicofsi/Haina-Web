<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyItemMedia extends Model
{

    use HasFactory;

    protected $table = 'company_item_media';

    public $timestamps = false;

    protected $fillable = [
        'id_company', 'name'
    ];


}