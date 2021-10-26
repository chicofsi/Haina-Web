<?php

namespace App\Http\Controllers\Api\Restaurant;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;

use GuzzleHttp\Client;
use GuzzleHttp\TransferStats;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;

use App\Http\Resources\ValueMessage;
use App\Http\Resources\RestaurantDataResource;

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
            'cuisine_type' => 'required',
            'restaurant_type' => 'required',
            'open_days' => 'required',
            'weekdays_time_open' => 'required',
            'weekdays_time_close' => 'required',
            'weekend_time_open' => 'required',
            'weekend_time_close' => 'required',
            'halal' => 'required',
            'menu_name' => 'required',
            ['menu_image' => 'required|image|mimes:png,jpg|max:53000'],
            ['restaurant_image' => 'required|image|mimes:png,jpg|max:5300']
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }
        else{
            $restaurant = [
                'name' => $request->name,
                'address' => $request->address,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'city_id' => $request->city_id,
                'phone' => $request->phone,
                'user_id' => Auth::id(),
                //'cuisine_type_id' => $request->cuisine_type_id,
                //'restaurant_type_id' => $request->restaurant_type_id,
                'open_days' => $request->open_days,
                'weekdays_time_open' => $request->weekdays_time_open,
                'weekdays_time_close' => $request->weekdays_time_close,
                'weekend_time_open' => $request->weekend_time_open,
                'weekend_time_close' => $request->weekend_time_close,
                'halal' => $request->halal,
                'verified' => 'pending'
            ];

            $new_restaurant = RestaurantData::create($restaurant);

            foreach($request->cuisine_type as $key => $value){
                $check_cuisine_type = RestaurantCuisineType::where('name', $value)->first();

                if($check_cuisine_type){
                    $new_restaurant->cuisine()->attach($check_cuisine_type);
                }
            }

            foreach($request->restaurant_type as $key => $value){
                $check_restaurant_type = RestaurantType::where('name', $value)->first();

                if($check_restaurant_type){
                    $new_restaurant->type()->attach($check_restaurant_type);
                }
            }

            $menu_name = $request->menu_name;
            $menu_images = $request->file('menu_image');

            $this->addMenu($new_restaurant['id'], $menu_name, $menu_images);

            $restaurant_images = $request->file('restaurant_image');

            $this->addRestaurantImages($new_restaurant['id'], $restaurant_images);

            $my_restaurant = new RestaurantDataResource($new_restaurant);
            return response()->json(new ValueMessage(['value'=>1,'message'=>'Register Restaurant Success!','data'=> $my_restaurant]), 200);

        }

    }

    public function myRestaurant(){
        $my_restaurant = RestaurantData::where('user_id', Auth::id())->with('cuisine', 'type')->get();

        if($my_restaurant){
            foreach($my_restaurant as $key => $value){
                $restaurant_data[$key] = new RestaurantDataResource($value); 
            }

            return response()->json(new ValueMessage(['value'=>1,'message'=>'Restaurant list displayed successfully!','data'=>$restaurant_data]), 200);
        }
        else{
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Restaurant not found!','data'=>'']), 404);
        }
    }

    public function showRestaurants(Request $request){

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

                    if($request->review_image != null){
                        $review_images = $request->file('review_image');
                        $this->storeReviewImages($new_review->id, $check_resto, $review_images);
                    }
                    
                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Add Review Success!','data'=> $new_review]), 200);
                }
            }
            else{
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Restaurant not found!','data'=>'']), 404);
            }
        }
    }


    public function addMenu($restaurant_id, $menu_name, $files){
        $restaurant = RestaurantData::where('id', $restaurant_id)->first();

        if($restaurant){
            $new_menu = RestaurantMenu::create([
                'restaurant_id' => $restaurant_id,
                'menu_name' => $menu_name
            ]);

            $num = 1;

            foreach($files as $file){

                $cleanname = str_replace(array( '\'', '"',',' , ';', '<', '>', '?', '*', '|', ':'), '', $restaurant['name']);
                $fileName = str_replace(' ','-', $restaurant['id'].'_'.$menu_name.'_'.$cleanname.'_'.$num);
                $guessExtension = $file->guessExtension();
                //dd($guessExtension);
                $store = Storage::disk('public')->putFileAs('restaurant/image/data/'.$restaurant['id'].'/menu'.'/'.$menu_name, $file ,$fileName.'.'.$guessExtension);

                $menu_images = RestaurantMenuPhotos::create([
                    'menu_id' => $new_menu['id'],
                    'filename' => $fileName,
                    'photo_url' => 'http://hainaservice.com/storage/'.$store
                ]);

                $num += 1; 
            }

            return response()->json(new ValueMessage(['value'=>1,'message'=>'Add Menu Success!','data'=> $new_menu]), 200);
        }
        else{
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Restaurant not found!','data'=>'']), 404);
        }
    }

    public function addRestaurantImages($restaurant_id, $files){
        $restaurant = RestaurantData::where('id', $restaurant_id)->first();

        if($restaurant){
            $num = 1;

            foreach($files as $file){
                $cleanname = str_replace(array( '\'', '"',',' , ';', '<', '>', '?', '*', '|', ':'), '', $restaurant['name']);
                $fileName = str_replace(' ','-', $restaurant['id'].'_'.$cleanname.'_'.$num);
                $guessExtension = $file->guessExtension();
                $store = Storage::disk('public')->putFileAs('restaurant/image/data/'.$restaurant['id'].'/gallery', $file ,$fileName.'.'.$guessExtension);

                $restaurant_images = RestaurantPhotos::create([
                    'restaurant_id' => $restaurant_id,
                    'filename' => $fileName,
                    'photo_url' => 'http://hainaservice.com/storage/'.$store
                ]);
            }
        }
        else{
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Restaurant not found!','data'=>'']), 404);
        }
    }

    public function storeReviewImages($id, $restaurant_id, $files){
        $restaurant = RestaurantData::where('id', $restaurant_id)->first();

        if($restaurant){

            $num = 1;

            foreach($files as $file){

                $cleanname = str_replace(array( '\'', '"',',' , ';', '<', '>', '?', '*', '|', ':'), '', $restaurant['name']);
                $fileName = str_replace(' ','-', 'review_'.$id.'_'.$restaurant['id'].'_'.$cleanname.'_'.$num);
                $guessExtension = $file->guessExtension();
                //dd($guessExtension);
                $store = Storage::disk('public')->putFileAs('restaurant/image/review/'.$id, $file ,$fileName.'.'.$guessExtension);

                $review_images = RestaurantReviewPhotos::create([
                    'review_id' => $id,
                    'filename' => $fileName,
                    'photo_url' => 'http://hainaservice.com/storage/'.$store
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