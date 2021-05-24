<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductGroup extends Model
{
    use HasFactory;

    protected $table = 'product_group';

    protected $fillable = [
    	 'id_product_category', 'name', 'photo_url', 'id_providers'
    ];


    public function productcategory(){
    	return $this->belongsTo('App\Models\ProductCategory','id_product_category','id');
    } 
    
    public function product(){
        return $this->hasMany('App\Models\Product','id_product_group','id');
    } 

    public function providers(){
        return $this->belongsTo('App\Models\Providers','id_providers','id');
    } 
}
