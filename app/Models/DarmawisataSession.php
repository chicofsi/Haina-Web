<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DarmawisataSession extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'darmawisata_session';
    protected $primaryKey = null;
    public $incrementing = false;
    protected $fillable = [
        'access_token', 'id_user'
    ];
    
    public function user(){
        return $this->belongsTo('App\Models\User','id_user','id');
    } 


}
