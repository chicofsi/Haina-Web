<?php

namespace App\Http\Controllers\Api\Post;

use App\Models\City;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ValueMessage;

class LocationController extends Controller
{
    public function getLocation()
    {
    	$location=City::select('id','name')->get();

		return response()->json(new ValueMessage(['value'=>1,'message'=>'Get Location Success!','data'=> $location]), 200);
    }

    
}
