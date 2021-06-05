<?php

namespace App\Http\Controllers\Api\Hotel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

use App\Models\HotelBooking;
use App\Models\User;
use App\Models\HotelRating;
use App\Http\Resources\HotelRatingResource; 
use App\Http\Resources\ValueMessage;

use DateTime;

class HotelRatingController extends Controller
{
    public function getRatingByHotel(Request $request){
        //nama rel, bukan table
        $post = HotelRating::with('hotel', 'users');

        if($request->has('hotel_id')){
            $post = $post->where('hotel_id', $request->hotel_id);
        }

        $post = $post->get();

        if($post->isEmpty()){
            return response()->json(new ValueMessage(['value'=>0, 'message'=>'Data Not Found!', 'data'=> '']), 404);
        }
        else{
            foreach($post as $key => $value){
                $postData[$key] = new HotelRatingResource($value);

            }

            return response()->json(new ValueMessage(['value'=>1, 'message'=>'Get Data Success!', 'data'=> $postData]), 200);
        }
    }

    public function getRatingByUser(Request $request){
        //nama rel, bukan table
        $post = HotelRating::with('hotel', 'users');

        $token = $request->header('Authorization');

        $tokens = DB::table('personal_access_tokens')->get();

        foreach ($tokens as $key => $val) {
            if(Str::contains($token, $val->id)){
                $success =  $val->tokenable_id;
                $user=User::where('id',$success)->first();

            }
        }

        if($user){
            $post = $post->where('user_id', $user->id);
    
            $post = $post->get();
    
            if($post->isEmpty()){
                return response()->json(new ValueMessage(['value'=>0, 'message'=>'Data Not Found!', 'data'=> '']), 404);
            }
            else{
                foreach($post as $key => $value){
                    $postData[$key] = new HotelRatingResource($value);
    
                }
    
                return response()->json(new ValueMessage(['value'=>1, 'message'=>'Get User Rating Success!', 'data'=> $postData]), 200);
            }
        }
        else{
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized!','data'=> '']), 403);
        }

        
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(new ValueMessage(['value'=>1,'message'=>'Request Success!','data'=> HotelRating::select('*')]), 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $token = $request->header('Authorization');

        $tokens = DB::table('personal_access_tokens')->get();

        foreach ($tokens as $key => $val) {
            if(Str::contains($token, $val->id)){
                $success =  $val->tokenable_id;
                $user=User::where('id',$success)->first();

            }
        }

        if($user){

            $checkbooking = HotelBooking::select('id')->where('user_id', $user->id)->where('hotel_id', $request->hotel_id)->first();

            if(!$checkbooking){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'No Valid Booking!','data'=> '']), 403);
            }
            else{
                
                $rating = HotelRating::create([
                    'user_id' => $user->id,
                    'hotel_id' => $request->hotel_id,
                    //'booking_id' => $checkbooking[0]['id'],
                    'rating' => $request->rating,
                    'review' => $request->review
                ]);
                

                return response()->json(new ValueMessage(['value'=>1,'message'=>'Add Rating Success','data'=> $rating]), 200);
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
        $token = $request->header('Authorization');

        $tokens = DB::table('personal_access_tokens')->get();

        foreach ($tokens as $key => $val) {
            if(Str::contains($token, $val->id)){
                $success =  $val->tokenable_id;
                $user=User::where('id',$success)->first();

            }
        }

        if($user){
            $checkbooking = HotelBooking::select('*')->where('user_id', $user->id)->where('id', $id)->first();

            if(!$checkbooking){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'No Valid Booking!','data'=> '']), 403);
            }
            else{
                
                //$now = new DateTime();
                
                $update = HotelRating::find($id);
                $update->rating = $request->rating;
                $update->review = $request->review;
                $update->save();
                //$update->updated_at = $now->format('Y-m-d H:i:s'); 


                return response()->json(new ValueMessage(['value'=>1,'message'=>'Update Rating Success','data'=> $update]), 200);
            }
        }
        else{
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized!','data'=> '']), 403);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $token = $request->header('Authorization');

        $tokens = DB::table('personal_access_tokens')->get();

        foreach ($tokens as $key => $val) {
            if(Str::contains($token, $val->id)){
                $success =  $val->tokenable_id;
                $user=User::where('id',$success)->first();

            }
        }

        if($user){
            $checkrating = HotelRating::select('*')->where('user_id', $user->id)->where('id', $id)->first();

            if(!$checkrating){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'No Valid Booking!','data'=> '']), 403);
            }
            else{
                
                //$now = new DateTime();
                
                $data = HotelRating::find($id);

                $data->delete();
                //$update->updated_at = $now->format('Y-m-d H:i:s'); 


                return response()->json(new ValueMessage(['value'=>1,'message'=>'Delete Rating Success','data'=> '']), 200);
            }
        }
        else{
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized!','data'=> '']), 403);
        }
    }
}
