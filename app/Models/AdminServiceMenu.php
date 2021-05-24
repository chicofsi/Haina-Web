<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminServiceMenu extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'admin_service_menu';

    protected $fillable = [
        'id_admin_menu','id_service', 'service_access',
    ];

    

    public function admin_menu(){
    	return $this->belongsTo('App\Models\AdminMenu','id_admin_menu');
    }
    public function service(){
        return $this->belongsTo('App\Models\Service','id_service');
    }
}
