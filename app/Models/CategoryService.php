<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryService extends Model
{
    use HasFactory;
    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */

    protected $table = 'service_category';

    protected $fillable = [
    	'name', 'icon_code',
    ];

    public function productCategory(){
    	return $this->hasMany('App\Models\ProductCategory','id_service_category', 'id');
    }

    public function getProduct(){
        return $this->hasMany('App\Models\ProductGroup','id_product_category', 'id');
    }
}
