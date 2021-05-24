<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminLogs extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'admin_logs';

    protected $fillable = [
        'id_admin', 'id_admin_activity', 'message'
    ];



    public function adminactivity(){
    	return $this->belongsTo('App\Models\AdminActivity','id_admin_activity','id');
    } 
    public function admin(){
        return $this->belongsTo('App\Models\Admin','id_admin','id');
    } 
}
