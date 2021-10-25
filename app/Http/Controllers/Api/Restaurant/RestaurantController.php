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
            'halal' => 'required',
            ['menu_image' => 'required|image|mimes:png,jpg|max:53000'],
            ['restaurant_image' => 'required|image|mimes:png,jpg|max:5300']
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }
        else{

        }

    }

    public function addReview(Request $request){
        $validator = Validator::make($request->all(), [
            'restraurant_id' => 'required',
            'rating' => 'required',
            'review' => 'required|min:50',
            ['review_image' => 'image|mimes:png,jpg|max:4096']
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }
        else{
            $check_resto = RestaurantData::where('id', $request->restaurant_id)->first();

            if($check_resto){
                if($check_resto['user_id'] == Auth::id()){
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Cannot review own restaurant!','data'=>'']), 403);
                }
                else{
                    $review = [
                        'user_id' => Auth::id(),
                        'restaurant_id' => $request->restaurant_id,
                        'rating' => $request->rating,
                        'review' => $request->review,
                    ];

                    $new_review = RestaurantReview::create($review);

                    $review_images = $request->file('review_image');
                    //$this->storeReviewImages($new_review->id, $check_resto, $review_images);

                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Add Review Success!','data'=> $new_review]), 200);
                }
            }
            else{
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Restaurant not found!','data'=>'']), 404);
            }
        }
    }

    public function storeReviewImages($id, $restaurant_id, $files){
        $restaurant = RestaurantData::where('id', $restaurant_id)->first();

        if($restaurant){

            $num = 1;

            foreach($files as $file){

                $cleanname = str_replace(array( '\'', '"',',' , ';', '<', '>', '?', '*', '|', ':'), '', $restaurant['name']);
                $fileName = str_replace(' ','-', $restaurant['id'].'_'.$cleanname.'_'.$num);
                $guessExtension = $file->guessExtension();
                //dd($guessExtension);
                $store = Storage::disk('public')->putFileAs('restaurant/image/review/'.$id, $file ,$fileName.'.'.$guessExtension);

                $review_images = RestaurantReviewImages::create([
                    'review_id' => $id,
                    'filename' => $fileName,
                    'path' => 'http://hainaservice.com/storage/'.$store
                ]);

                $num += 1; 
            }

            return response()->json(new ValueMessage(['value'=>1,'message'=>'Upload Review Image Success!','data'=> '']), 200);

        }
        else{
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Restaurant not found!','data'=>'']), 404);
        }
    }

}