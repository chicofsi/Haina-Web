<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyItemCategory extends Model
{

    use HasFactory;

    protected $table = 'company_item_category';


    protected $fillable = [
        'id', 'name', 'name_zh'
    ];


}