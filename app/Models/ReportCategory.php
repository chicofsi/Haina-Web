<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportCategory extends Model
{
    use HasFactory;
    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */

    protected $table = 'report_category';

    protected $fillable = [
    	'title', 'icon', 'default_message', 'id_report_for'
    ];

    public function userreport(){
    	return $this->hasMany('App\Models\UserReport','id_report_category','id');
    }
    public function reportfor(){
        return $this->belongsTo('App\Models\ReportFor','id_report_for','id');
    } 
}
