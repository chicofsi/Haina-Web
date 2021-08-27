<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForumReport extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'forum_report';

    protected $fillable = [
        'id_user_reporter','id_user_reported', 'id_report_category','message'
    ];

    public function reportcategory(){
    	return $this->belongsTo('App\Models\ReportCategory','id_report_category','id');
    } 
    public function reporter(){
        return $this->belongsTo('App\Models\User','id_user_reporter','id');
    }
    public function reported(){
        return $this->belongsTo('App\Models\User','id_user_reported','id');
    }
}