<?php

namespace App\Http\Controllers\Api\Hotel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Models\HotelImage;
use App\Http\Resources\HotelImageResource;
use App\Http\Resources\ValueMessage;

class HotelImageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function getImageByHotel(Request $request){
        $post = HotelImage::select('*');

        if($request->has('hotel_id')){
            $post = $post->where('hotel_id', $request->hotel_id);
        }
        
        $post = $post->get();

        if($post->isEmpty()){
            return response()->json(new ValueMessage(['value'=>0, 'message'=>'Data Not Found!', 'data'=> '']), 404);
        }
        else{
            foreach($post as $key => $value){
                $postData[$key] = new HotelImageResource($value);

            }

            return response()->json(new ValueMessage(['value'=>0, 'message'=>'Get Data Success!', 'data'=> $postData]), 200);
        }
    }

    public function index()
    {
        
        return response()->json(new ValueMessage(['value'=>1,'message'=>'Request Success!','data'=>HotelImage::all()]), 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return response()->json(new ValueMessage(['value'=>1,'message'=>'Request Success!','data'=>HotelImage::find($id)]), 200);
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
        //
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
