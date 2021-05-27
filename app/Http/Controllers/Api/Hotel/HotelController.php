<?php

namespace App\Http\Controllers\Api\Hotel;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Models\Hotel;
use App\Http\Resources\HotelResource;
use App\Http\Resources\HotelDetailResource; 
use App\Http\Resources\ValueMessage;

class HotelController extends Controller
{

    public function getHotelByCity(Request $request){
        $post = Hotel::with('city', 'facilities');

        if($request->has('city_id')){
            $post = $post->where('city_id', $request->city_id);
        }
        
        $post = $post->get();

        if($post->isEmpty()){
            return response()->json(new ValueMessage(['value'=>0, 'message'=>'Data Not Found!', 'data'=> '']), 404);
        }
        else{
            foreach($post as $key => $value){
                $postData[$key] = new HotelResource($value);
            }

            return response()->json(new ValueMessage(['value'=>1, 'message'=>'Get Data Success!', 'data'=> $postData]), 200);
        }
    }

    public function getHotelByName(Request $request){
        $post = Hotel::with('city', 'facilities');

        if($request->has('search_query')){
            $post = $post->where('hotel_name', 'like', '%' . $request->search_query . '%');
        }
        
        $post = $post->get();

        if($post->isEmpty()){
            return response()->json(new ValueMessage(['value'=>0, 'message'=>'Data Not Found!', 'data'=> '']), 404);
        }
        else{
            foreach($post as $key => $value){
                $postData[$key] = new HotelResource($value);

            }

            return response()->json(new ValueMessage(['value'=>1, 'message'=>'Get Data Success!', 'data'=> $postData]), 200);
        }
    }

    

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $post = Hotel::select('*');

        $post = $post->get();

        if($post->isEmpty()){
            return response()->json(new ValueMessage(['value'=>0, 'message'=>'Data Not Found!', 'data'=> '']), 404);
        }
        else{
            foreach($post as $key => $value){
                $postData[$key] = new HotelResource($value);

            }

            return response()->json(new ValueMessage(['value'=>1, 'message'=>'Get Data Success!', 'data'=> $postData]), 200);
        }
        
        //return response()->json(new ValueMessage(['value'=>1,'message'=>'Request Success!','data'=>  Hotel::all()]), 200);
        
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
        //nama rel, bukan table
        $post = Hotel::find($id)->with('facilities', 'room');
        $post = $post->where('id', $id);
        $post = $post->get();

        if($post->isEmpty()){
            return response()->json(new ValueMessage(['value'=>0, 'message'=>'Data Not Found!', 'data'=> '']), 404);
        }
        else{
            foreach($post as $key => $value){
                $postData[$key] = new HotelResource($value);

            }

            return response()->json(new ValueMessage(['value'=>1, 'message'=>'Get Data Success!', 'data'=> $postData]), 200);
        }

        //return response()->json(new ValueMessage(['value'=>1,'message'=>'Request Success!','data'=>  Hotel::with('facilities')->find($id)]), 200);
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
        $post = Hotel::find($id);
        
        $post = $post->first();

        $post->update($request->all());
        $post->save();

        return response()->json(new ValueMessage(['value'=>1, 'message'=>'Update Data Success!', 'data'=> $post]), 200);
        
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
