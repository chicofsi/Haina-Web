<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyPhoto extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'company_photo';

    protected $fillable = [
        'id_company', 'photo_url', 'name'
    ];



    
    public function company(){
        return $this->belongsTo('App\Models\Company','id_company','id');
    } 
}
