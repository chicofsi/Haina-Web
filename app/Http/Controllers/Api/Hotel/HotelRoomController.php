<?php

namespace App\Http\Controllers\Api\Hotel;

use Illuminate\Http\Request;
use App\Models\HotelRoom;
use App\Http\Resources\HotelRoomResource; 
use App\Http\Resources\ValueMessage;

class HotelRoomController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(new ValueMessage(['value'=>1,'message'=>'Request Success!','data'=>HotelRoom::all()]), 200);
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
        return response()->json(new ValueMessage(['value'=>1,'message'=>'Request Success!','data'=>HotelRoom::find($id)]), 200);
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
        $post = HotelRoom::where('id', $id);

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
