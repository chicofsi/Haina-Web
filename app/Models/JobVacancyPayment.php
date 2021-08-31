<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobVacancyPayment extends Model
{
    use HasFactory;

    protected $table = 'job_vacancy_payment';

    protected $fillable = [
        'id_vacancy', 'price', 'payment_method_id', 'order_id', 'midtrans_id', 'va_number', 'settlement_time', 
        'payment_status'
    ];

    public function vacancy(){
        return $this->belongsTo('App\Models\JobVacancy', 'id_vacancy', 'id');
    }

    public function paymentMethod(){
        return $this->belongsTo('App\Models\PaymentMethod', 'id', 'payment_method_id');
    }
}