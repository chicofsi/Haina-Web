<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminMenu extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'admin_menu';

    protected $fillable = [
        'menu_name', 'route', 'active','admin_access',
    ];



    public function admin_service_menu(){
    	return $this->hasMany('App\Models\AdminServiceMenu','id','id_admin_menu');
    }
}
