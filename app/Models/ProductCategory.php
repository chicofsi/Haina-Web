<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    use HasFactory;
    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */

    protected $table = 'product_category';

    protected $fillable = [
    	'name', 'photo_url', 'name_zh'
    ];

    public function productgroup(){
    	return $this->hasMany('App\Models\ProductGroup','id_product_category','id');
    }
}
