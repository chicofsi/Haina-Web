<?php

namespace App\Http\Controllers\Api\Property;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

use App\Models\City;
use App\Models\User;
use App\Models\PropertyData;
use App\Models\PropertyImageData;
use App\Models\PropertyTransaction;

use DateTime;

use App\Http\Resources\ValueMessage;

class PropertyDataController extends Controller
{

    public function showMyProperty(){
        $property = PropertyData::where('id_user', Auth::user()->id)->with('images')->get();

        if(!$property){
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Property Not Found!','data'=> '']), 404);
        }
        else{
            return response()->json(new ValueMessage(['value'=>1,'message'=>'Property loaded successfully!','data'=> $property]), 200);
        }
    }

    public function addProperty(Request $request){
        $validator = Validator::make($request->all(), [
            'property_type' => 'in:office, warehouse, house, apartment',
            'name' => 'required',
            'condition' => 'required',
            'year' => 'required',
            'id_city' => 'required',
            'address' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'selling_price' => 'required',
            'rental_price' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }
        else{
            try{

                $property = [
                    'id_user' => Auth::id(),
                    'property_type' => $request->property_type,
                    'name' => $request->name,
                    'condition' => $request->condition,
                    'year' => $request->year,
                    'id_city' => $request->id_city,
                    'address' => $request->address,
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                    'selling_price' => $request->selling_price,
                    'rental_price' => $request->rental_price,
                    'post_date' => date("Y-m-d H:i:s"),
                    'status' => 'available'
                ];

                $newproperty = PropertyData::create($property);

                return response()->json(new ValueMessage(['value'=>1,'message'=>'Create Property Success!','data'=> $property]), 200);

            }
            catch(Exception $e){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Error Creating Property!','data'=> '']), 404);
            }
        }
    }

    public function showAvailableProperty(){
        $property = PropertyData::where('id_user', 'not like', Auth::user()->id)->with('images')->get();

        if(!$property){
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Property Not Found!','data'=> '']), 404);
        }
        else{
            return response()->json(new ValueMessage(['value'=>1,'message'=>'Property loaded successfully!','data'=> $property]), 200);
        }
    }

}