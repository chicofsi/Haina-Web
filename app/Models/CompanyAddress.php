<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyAddress extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'company_address';

    protected $fillable = [
        'id_company', 'address', 'active', 'id_city'
    ];



    
    public function company(){
        return $this->belongsTo('App\Models\Company','id_company','id');
    } 
    public function city(){
        return $this->belongsTo('App\Models\City','id_city','id');
    } 
}
