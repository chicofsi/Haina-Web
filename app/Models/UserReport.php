<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserReport extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'user_report';

    protected $fillable = [
        'id_user_reporter','id_user_reported', 'id_report_category'
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

    public function subforum(){
        return $this->belongsToMany('App\Models\Subforum', 'report_list_subforum', 'report_id', 'subforum_id');
    }

    public function post(){
        return $this->belongsToMany('App\Models\ForumPost', 'report_list_post', 'report_id', 'post_id');
    }

    public function comment(){
        return $this->belongsToMany('App\Models\ForumComment', 'report_list_comment', 'report_id', 'comment_id');
    }

    public function company(){
        return $this->belongsToMany('App\Models\Company', 'report_list_company', 'report_id', 'company_id');
    }

    public function property(){
        return $this->belongsToMany('App\Models\PropertyData', 'report_list_property', 'report_id', 'property_id');
    }

    public function profile(){
        return $this->belongsToMany('App\Models\User', 'report_list_profile', 'report_id', 'user_id');
    }
}
