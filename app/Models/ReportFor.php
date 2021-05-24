<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportFor extends Model
{
    use HasFactory;
    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */

    protected $table = 'report_for';

    protected $fillable = [
    	'name', 
    ];

    public function reportcategory(){
    	return $this->hasMany('App\Models\ReportCategory','id_report_for','id');
    }
    
}
