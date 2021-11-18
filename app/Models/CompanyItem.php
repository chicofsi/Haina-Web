<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyItem extends Model
{

    use HasFactory;

    protected $table = 'company_item';


    protected $fillable = [
        'id_item_catalog', 'id_item_category', 'item_name', 'item_description', 'item_price'
    ];


}