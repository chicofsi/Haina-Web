<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'company';

    protected $fillable = [ 
        'id_user', 'name', 'description', 'icon_url', 'year', 'staff_size', 'status', 'siup', 'id_province', 
    ];



    public function jobvacancy(){
    	return $this->hasMany('App\Models\JobVacancy','id_company','id');
    }
    public function address(){
        return $this->hasMany('App\Models\CompanyAddress','id_company','id');
    }
    public function photo(){
        return $this->hasMany('App\Models\CompanyMedia','id_company','id');
    }
    public function user(){
        return $this->belongsTo('App\Models\User','id_user','id');
    }
    public function category(){
        return $this->belongsToMany(CompanyCategory::class, 'company_category_data', 'id_company', 'id_company_category');
    }
}
