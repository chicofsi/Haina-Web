<?php

namespace App\Http\Controllers\Api\Post\Jobs\v2;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

use GuzzleHttp\Client;
use GuzzleHttp\TransferStats;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;

use App\Http\Resources\ValueMessage;

use App\Models\NotificationCategory;
use App\Models\PersonalAccessToken;
use App\Models\User;
use App\Models\UserNotification;
use App\Models\RestaurantData;
use App\Models\RestaurantCuisineType;
use App\Models\RestaurantType;
use App\Models\RestaurantPhotos;
use App\Models\RestaurantMenu;
use App\Models\RestaurantMenuPhotos;
use App\Models\RestaurantReview;
use App\Models\RestaurantReviewPhotos;

use App\Http\Controllers\Api\Notification\NotificationController;

class RestaurantController extends Controller
{

    public function registerNewRestaurant(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'address' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'city_id' => 'required',
            'phone' => 'required',
            'user_id' => 'required',
            'cuisine_type_id' => 'required',
            'restaurant_type_id' => 'required',
            'open_days' => 'required',
            'weekdays_time_open' => 'required',
            'weekdays_time_close' => 'required',
            'weekend_time_open' => 'required',
            'weekend_time_close' => 'required',
            'halal' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }
        else{
            
        }

    }

}