<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HowToPay extends Model
{
    use HasFactory;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'how_to_pay';

    protected $fillable = [ 
        'id_payment_method', 'payment_media', 'how_to', 'how_to_zh'
    ];

    public function payment_method(){
        return $this->belongsTo('App\Models\PaymentMethod', 'id', 'id_payment_method');
    }
    

}
