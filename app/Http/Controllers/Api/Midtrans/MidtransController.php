<?php

namespace App\Http\Controllers\Api\Midtrans;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ValueMessage;
use Illuminate\Support\Facades\Validator;

use App\Models\Transaction;
use App\Models\TransactionPayment;
use App\Models\PaymentMethod;
use App\Models\NotificationCategory;
use App\Models\HotelBooking;
use App\Models\HotelBookingPayment;


class MidtransController extends Controller
{
	public function notificationHandler (Request $request)
    {
        $order_id=$request->order_id;
        $transaction_id=$request->transaction_id;
        $method=$request->payment_type;
        $transaction_time=$request->transaction_time;
        $transaction_status=$request->transaction_status;
        $status_code=$request->status_code;
        $gross_amount=$request->gross_amount;

        if($request->custom_field1=="PPOB"){

            $status="";
            if($transaction_status=='settlement'){
                $settlement_time=date("Y-m-d h:m:s",strtotime($request->settlement_time));
                $status='process';
            }else if($transaction_status=='pending'){
                $settlement_time=null;
                $status='pending payment';
            }else if($transaction_status=='expire'){
                $settlement_time=null;
                $status='unsuccess';
            }else if($transaction_status=='cancel'){
                $settlement_time=null;
                $status='unsuccess';
            }

            $transaction=Transaction::where('order_id',$order_id)->update(['status'=>$status]);
            $transaction=Transaction::where('order_id',$order_id)->with('product')->first();

            foreach ($request['va_numbers'] as $key => $value) {
                $va_number=$value['va_number'];
                $payment=PaymentMethod::where('name',$value['bank'])->first();
            }

            $transactionpayment=TransactionPayment::updateOrCreate(
                [
                    'id_transaction' => $transaction->id
                ],
                [
                    'midtrans_id' => $transaction_id,
                    'id_payment_method' => $payment->id,
                    'settlement_time' => $settlement_time,
                    'payment_status' => $transaction_status,
                    'va_number' => $va_number
                ]);
            return $transactionpayment;
        }else if($request->custom_field1=="Hotel"){
            $status="";
            if($transaction_status=='settlement'){
                $settlement_time=date("Y-m-d h:m:s",strtotime($request->settlement_time));
                $status='PAID';
            }else if($transaction_status=='pending'){
                $settlement_time=null;
                $status='UNPAID';
            }else if($transaction_status=='expire'){
                $settlement_time=null;
                $status='CANCELLED';
            }else if($transaction_status=='cancel'){
                $settlement_time=null;
                $status='CANCELLED';
            }

            $hotelbooking=HotelBooking::where('order_id',$order_id)->update(['status'=>$status]);
            $hotelbooking=HotelBooking::where('order_id',$order_id)->with('hotel','room')->first();

            foreach ($request['va_numbers'] as $key => $value) {
                $va_number=$value['va_number'];
                $payment=PaymentMethod::where('name',$value['bank'])->first();
            }

            $hotelbookingpayment=HotelBookingPayment::updateOrCreate(
                [
                    'booking_id' => $hotelbooking->id
                ],
                [
                    'midtrans_id' => $transaction_id,
                    'payment_method_id' => $payment->id,
                    'settlement_time' => $settlement_time,
                    'payment_status' => $transaction_status,
                    'va_number' => $va_number
                ]);
            return $hotelbookingpayment;
        }

    }
}
