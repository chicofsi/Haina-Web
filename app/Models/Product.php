<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'product';

    protected $fillable = [
    	 'id_product_group', 'product_code', 'description', 'base_price', 'sell_price', 'inquiry_type'
    ];


    public function productgroup(){
    	return $this->belongsTo('App\Models\ProductGroup','id_product_group','id');
    } 
    
    public function transaction(){
        return $this->hasMany('App\Models\Transaction','id_product','id');
    } 
}
