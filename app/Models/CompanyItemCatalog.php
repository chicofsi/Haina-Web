<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyItemCatalog extends Model
{

    use HasFactory;

    protected $table = 'company_item_catalog';

    protected $fillable = [
        'id_company', 'name'
    ];

    public function items(){
    	return $this->hasMany('App\Models\CompanyItem','id_item_catalog','id');
    }

}