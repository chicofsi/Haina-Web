<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminActivity extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'admin_activity';

    protected $fillable = [
        'id_admin_activity_category', 'name', 'default_message'
    ];



    public function adminactivitycategory(){
    	return $this->belongsTo('App\Models\AdminActivityCategory','id_admin_activity_category','id');
    } 
    public function adminlogs(){
        return $this->hasMany('App\Models\AdminLogs','id_admin_activity','id');
    }
}
