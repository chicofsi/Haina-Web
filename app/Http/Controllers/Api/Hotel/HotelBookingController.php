<?php

namespace App\Http\Controllers\Api\Hotel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

use App\Models\HotelBooking;
use App\Models\HotelBookingPayment;
use App\Models\HotelRating;
use App\Models\User;
use App\Models\PaymentMethod;
use App\Models\PaymentMethodCategory;
use App\Http\Resources\HotelBookingResource; 
use App\Http\Resources\ValueMessage;
use DateTime;

class HotelBookingController extends Controller
{

    //bikin baru
    public function getBooking(Request $request){
        $post=HotelBooking::select('*');

        if($request->has('id')){
            $post = $post->where('id', $request->id);
        }
        if($request->has('hotel_id')){
            $post = $post->where('hotel_id', $request->hotel_id);
        }
        if($request->has('status')){
            $post = $post->where('status', $request->status);
        }
        
        $post = $post->get();

        if($post->isEmpty()){
            return response()->json(new ValueMessage(['value'=>0, 'message'=>'Data Not Found!', 'data'=> '']), 404);
        }
        else{
            foreach($post as $key => $value){
                $postData[$key] = new HotelBookingResource($value);

            }

            return response()->json(new ValueMessage(['value'=>1, 'message'=>'Get Data Success!', 'data'=> $postData]), 200);
        }
    }

    public function getBookingByUser(Request $request){
        $token=$request->header('Authorization');

        $tokens = DB::table('personal_access_tokens')->get();

        foreach ($tokens as $key => $val) {
            if(Str::contains($token, $val->id)){
                $success =  $val->tokenable_id;
                $user=User::where('id',$success)->first();

            }
        }

        if($user){
            //$post=HotelBooking::select('*');

            //$post = $post->where('user_id', $user->id);
            //$post = $post->get();

            $paidtrans = HotelBooking::where('user_id', $user->id)->with('hotel', 'payment')->where('status', 'PAID')->orderBy('updated_at', 'DESC')->get();
            $unpaidtrans = HotelBooking::where('user_id', $user->id)->with('hotel', 'payment')->where('status', 'UNPAID')->orderBy('updated_at', 'DESC')->get();
            $canceltrans = HotelBooking::where('user_id', $user->id)->with('hotel', 'payment')->where('status', 'CANCELLED')->orderBy('updated_at', 'DESC')->get();

            foreach ($paidtrans as $key => $val){
                $rating = HotelRating::where('user_id', $user->id)->where('hotel_id', $val->hotel_id)->first();
                
                $paidtrans[$key]['rating'] = $rating;
            }

            $data['paid'] = $paidtrans->values();
            $data['unpaid'] = $unpaidtrans->values();
            $data['cancelled'] = $canceltrans->values();
            
            if($data['paid']->isEmpty() && $data['unpaid']->isEmpty() && $data['cancelled']->isEmpty()){
                return response()->json(new ValueMessage(['value'=>1, 'message'=>'Booking Data Not Found!', 'data'=> '']), 404); 
            }
            else{
                return response()->json(new ValueMessage(['value'=>1, 'message'=>'Get Data Success!', 'data'=> $data]), 200); 
            }
            

        
        }
        else{
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized!','data'=> '']), 503);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(new ValueMessage(['value'=>1,'message'=>'Request Success!','data'=>HotelBooking::get()]), 200);
    }


    //MidTrans
    public function chargeMidtrans($transaction,$payment)
	{
		$username="SB-Mid-server-uUu-OOYw1hyxA9QH8wAbtDRl";
		$url="https://api.sandbox.midtrans.com/v2/charge";
		$data_array =  [
		    "payment_type"        => $payment->category->url,
		    "bank_transfer"       => [
		    	"bank"               => $payment->name
		    ],
            "custom_field1"        => "Hotel",
		    "transaction_details" => array(
		        "order_id"            => $transaction->order_id,
		        "gross_amount"		  => $transaction->total_price
		    ),
		];

		$header="Authorization: Basic ".base64_encode($username.":");
		// return json_encode($data_array)."BLABLABLAB".$header."davdavd".$username.":";
		$make_call = $this->callAPI($url, json_encode($data_array),$header);
		return $make_call;
	}

    //callAPI
    function callAPI( $url, $data, $header = false){
		$curl = curl_init();
      	curl_setopt($curl, CURLOPT_POST, 1);
      	if ($data)
      	   	curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

		// OPTIONS:
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		if(!$header){
	       	curl_setopt($curl, CURLOPT_HTTPHEADER, array(
	       	   	'Content-Type: application/json',
	       	));
	   	}else{
	   	    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
	   	       	'Content-Type: application/json',
	   	       	$header
	   	    ));
	   	}
		// EXECUTE:
		$result = curl_exec($curl);
		if(!$result){die("Connection Failure");}
		curl_close($curl);
		return $result;
	}

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
        $token=$request->header('Authorization');

        $tokens = DB::table('personal_access_tokens')->get();

        foreach ($tokens as $key => $val) {
            if(Str::contains($token, $val->id)){
                $success =  $val->tokenable_id;
                $user=User::where('id',$success)->first();

            }
        }

        if($user){
            $datein = new DateTime (date("Y-m-d",strtotime($request->check_in)));
            $dateout = new DateTime (date("Y-m-d",strtotime($request->check_out)));
            $interval = $datein->diff($dateout);
            $days = $interval->format('%a');

            do{
                $order_id = Str::random(10);
                $order_id = strtoupper($order_id);
                $checking_id = HotelBooking::where('order_id', $order_id)->get();
            }
            while(!$checking_id->isEmpty());

            $idbooking = $order_id; 

            //$request->check_in = date("Y-m-d",strtotime($request->check_in));
            //$request->check_out = date("Y-m-d",strtotime($request->check_out));

            //date("Y-m-d",strtotime($request->date))

            $validator = Validator::make($request->all(), [
                'hotel_id' => 'required|integer|exists:hotel,id',
                'room_id' => 'required|integer|exists:hotel_rooms,id',
                //'user_id' => 'required|integer|exists:users,id',
                'check_in' => 'required|after_or_equal:today',
                'check_out' => ['required', function($attribute, $value, $fail){
                    if(strtotime($value) <= strtotime(date("Y-m-d"))){
                        $fail('Invalid check out date');
                    }
                }],
                //'total_night' => 'integer|min: 1',
                'total_guest' => 'required|integer|min: 1',
                'total_price' => 'required|integer'
            ]);

            if ($validator->fails()) {          
                return response()->json(['error'=>$validator->errors()], 400);              
            }
            else{

                $payment = PaymentMethod::where('id',$request->id_payment_method)->with('category')->first();

                $booking = HotelBooking::create(
                    [
                        'hotel_id' => $request->hotel_id,
                        'room_id' => $request->room_id,
                        'user_id' => $user->id,
                        'check_in' => date("Y-m-d",strtotime($request->check_in)),
                        'check_out' => date("Y-m-d",strtotime($request->check_out)),
                        'total_night' => (int)$days,
                        'total_guest' => $request->total_guest,
                        'total_price' => $request->total_price,
                        'status' => "UNPAID",
                        'order_id' => $idbooking,
                        'transaction_time' => date("Y-m-d h:m:s")
                    ]
                );
                //
                $booking['payment_data'] = json_decode($this->chargeMidtrans($booking, $payment));

                if($booking){
                    $booking_data = HotelBooking::where('id',$booking->id)->first();
                    
                    $data['payment_type'] = $booking->payment_data->payment_type;
                    $data['amount']=$booking->payment_data->gross_amount;
                    $data['payment_status']=$booking->payment_data->transaction_status;
                    foreach ($booking->payment_data->va_numbers as $key => $value) {
                        $data['virtual_account']=$value->va_number;
                        $data['bank']=$value->bank;
                    }

                    $booking_data['payment'] = $data;
                }
                
                //dd($booking_data);

                $hotel_payment = HotelBookingPayment::create([
                    'booking_id' => $booking_data->id,
                    'payment_method_id' => $request->id_payment_method,
                    'midtrans_id' => '',
                    'va_number' => $booking_data->payment['virtual_account'],
                    'settlement_time' => null,
                    'payment_status' => 'pending'
                ]);

                //

                return response()->json(new ValueMessage(['value'=>1,'message'=>'Create Booking Success!','data'=>  $booking_data, $hotel_payment]), 200);
            }
        }
        else{
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized!','data'=> '']), 503);
        }        
  
    }

    //cancel booking
    public function cancel(Request $request)
    {
        $token=$request->header('Authorization');

        $tokens = DB::table('personal_access_tokens')->get();

        foreach ($tokens as $key => $val) {
            if(Str::contains($token, $val->id)){
                $success =  $val->tokenable_id;
                $user=User::where('id',$success)->first();

            }
        }
        if($user){
            $booking = HotelBooking::find($request->id);

            if($booking->user_id == $user->id){
                $booking->status = "CANCELLED";
                $booking->save();

                return response()->json(new ValueMessage(['value'=>1,'message'=>'Cancel Booking Success!', 'data'=> $booking ]), 200);
            }
            else{
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized Transaction!','data'=> '']), 403);
            }
            
        }
        else{
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized!','data'=> '']), 403);
        }

        
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //return response()->json(new ValueMessage(['value'=>1,'message'=>'Request Success!','data'=>  HotelBooking::find($id)]), 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $booking = HotelBooking::find($id);

        $datein = new DateTime (date("Y-m-d",strtotime($request->check_in)));
        $dateout = new DateTime (date("Y-m-d",strtotime($request->check_out)));
        $interval = $datein->diff($dateout);
        $days = $interval->format('%a');

        $validator = Validator::make($request->all(), [
            'room_id' => 'sometimes|required|integer|exists:hotel_rooms,id',
            'check_in' => 'required|after_or_equal:today',
            'check_out' => ['required', function($attribute, $value, $fail){
                if(strtotime($value) <= strtotime(date("Y-m-d"))){
                    $fail('Invalid check out date');
                }
            }],
            'total_guest' => 'sometimes|required|integer|min: 1',
            'total_price' => 'sometimes|required|integer'
        ]);

        if ($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 400);                        
        }
        else{
            $booking->update($request->all());
            //$booking->check_in = $request->check_in;
            //$booking->check_out = $request->check_out;
            $booking->total_night = (int)$days;
            $booking->save();

            return response()->json(new ValueMessage(['value'=>1,'message'=>'Update Booking Success!', 'data'=> $booking ]), 200);
        }
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
