<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyData extends Model
{
    use HasFactory;

    protected $table = 'property_transaction';

    protected $fillable = [
    	 'id_owner', 'id_buyer_tenant', 'id_property', 'transaction_date', 'transaction_type', 'transaction_status'
    ];

    public function owner(){
    	return $this->belongsTo('App\Models\User','id_owner','id');
    }

    public function buyer(){
    	return $this->belongsTo('App\Models\User','id_buyer_tenant','id');
    }

    public function property(){
    	return $this->belongsTo('App\Models\PropertyData','id_property','id');
    }

}