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

use DateTime;

use App\Http\Resources\ValueMessage;
use App\Http\Resources\RestaurantDataResource;
use App\Http\Resources\RestaurantMenuResource;
use App\Http\Resources\RestaurantReviewResource;

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
            'halal' => 'required|gte:0|lte:1',
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
                'open' => 1,
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

    public function updateRestaurant(Request $request){
        $validator = Validator::make($request->all(), [
            'restaurant_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }
        else{
            $check_resto = RestaurantData::where('id', $request->restaurant_id)->first();

            if($check_resto && $check_resto['user_id'] == Auth::id()){
                $update_restaurant = RestaurantData::where('id', $request->restaurant_id)->update([
                    'name' => $request->name ?? $check_resto['name'],
                    'address' => $request->address ?? $check_resto['address'],
                    'latitude' => $request->latitude ?? $check_resto['latitude'],
                    'longitude' => $request->longitude ?? $check_resto['longitude'],
                    'city_id' => $request->city_id ?? $check_resto['city_id'],
                    'phone' => $request->phone ?? $check_resto['phone'],
                    'open_days' => $request->open_days ?? $check_resto['open_days'],
                    'weekdays_time_open' => $request->weekdays_time_open ?? $check_resto['weekdays_time_open'],
                    'weekdays_time_close' => $request->weekdays_time_close ?? $check_resto['weekdays_time_close'],
                    'weekend_time_open' => $request->weekend_time_open ?? $check_resto['weekend_time_open'],
                    'weekend_time_close' => $request->weekend_time_close ?? $check_resto['weekend_time_close'],
                    'halal' => $request->halal ?? $check_resto['halal'],
                    'open' => $request->open ?? $check_resto['open'],
                ]);

                $cuisine_data = [];
                $type_data = [];

                if($request->cuisine_type != null){
                    foreach($request->cuisine_type as $key => $value){
                        $check_cuisine_type = RestaurantCuisineType::where('name', $value)->first();
        
                        if($check_cuisine_type){
                            array_push($cuisine_data, $check_cuisine_type['id']);
                        }
                    }

                    $check_resto->cuisine()->sync($cuisine_data);
                }
    
                if($request->restaurant_type != null){
                    foreach($request->restaurant_type as $key => $value){
                        $check_restaurant_type = RestaurantType::where('name', $value)->first();
        
                        if($check_restaurant_type){
                            array_push($type_data, $check_restaurant_type['id']);
                        }
                    }

                    $check_resto->type()->sync($type_data);
                }

                $updated_data = RestaurantData::where('id', $request->restaurant_id)->first();
                $data = new RestaurantDataResource($updated_data);

                return response()->json(new ValueMessage(['value'=>1,'message'=>'Restaurant data updated successfully!','data'=>$data]), 200);
            }
            else{
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Restaurant not found / Unauthorized!','data'=>'']), 404);
            }
        }
    }

    public function showRestaurants(Request $request){
        $all_restaurant = RestaurantData::where('verified', '!=', 'pending')->with('cuisine', 'type')->get();

        if($request->cuisine_type != null){
            $all_restaurant = $all_restaurant->whereHas('cuisine', function ($q){

                $q->where('name', $request->cuisine_type);
            });
        }
        if($request->restaurant_type != null){
            $all_restaurant = $all_restaurant->whereHas('type', function ($q){

                $q->where('name', $request->restaurant_type);
            });
        }
        if($request->city_id != null){
            $all_restaurant = $all_restaurant->where('city_id', $request->city_id);
        }

        if(count($all_restaurant) > 0){
            foreach($all_restaurant as $key => $value){
                $restaurant_data[$key] = new RestaurantDataResource($value); 
            }

            $total = count($restaurant_data);
            $per_page = 10;
            $current_page = $request->page ?? 1;

            $starting_point = ($current_page * $per_page) - $per_page;
            $restaurant_data = array_slice($restaurant_data, $starting_point, $per_page);

            $result = new \stdClass();
            $result->restaurants = $restaurant_data;
            $result->total = $total;
            $result->current_page = (int)$current_page;
            $result->total_page = ceil($total/$per_page);

            if(count($restaurant_data) == 0){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'No restaurants found!','data'=> '']), 404);
            }
            else{
                //$object = new \stdClass();
                //$threads->followed = SubforumFollower

                return response()->json(new ValueMessage(['value'=>1,'message'=>'Restaurants list displayed successfully!','data'=> $result]), 200);
            }

            //return response()->json(new ValueMessage(['value'=>1,'message'=>'Restaurant list displayed successfully!','data'=>$restaurant_data]), 200);
        }
        else{
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Restaurant not found!','data'=>'']), 404);
        }
    }

    public function detailRestaurant(Request $request){
        $validator = Validator::make($request->all(), [
            'restaurant_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }
        else{
            $check_resto = RestaurantData::where('id', $request->restaurant_id)->first();

            if($check_resto){
                $restaurant_data = new RestaurantDataResource($check_resto); 

                return response()->json(new ValueMessage(['value'=>1,'message'=>'Restaurant data displayed successfully!','data'=>$restaurant_data]), 200);
            }
            else{
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Restaurant not found!','data'=>'']), 404);
            }
        }
    }

    public function menuRestaurant(Request $request){
        $validator = Validator::make($request->all(), [
            'restaurant_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }
        else{
            $check_resto = RestaurantData::where('id', $request->restaurant_id)->first();

            if($check_resto){
                $check_menu = RestaurantMenu::where('restaurant_id', $request->restaurant_id)->where('deleted_at', null)->get();

                if(count($check_menu) > 0){
                    foreach($check_menu as $key => $value){
                        $menu_data[$key] = new RestaurantMenuResource($value); 
                    }

                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Menu data displayed successfully!','data'=>$menu_data]), 200);
                }
                else{
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Menu not found!','data'=>'']), 404);
                }
            }
            else{
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Restaurant not found!','data'=>'']), 404);
            }
        }
    }

    public function reviewRestaurant(Request $request){
        $validator = Validator::make($request->all(), [
            'restaurant_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }
        else{
            $check_resto = RestaurantData::where('id', $request->restaurant_id)->first();

            if($check_resto){
                $check_review = RestaurantReview::where('restaurant_id', $request->restaurant_id)->where('deleted_at', null)->get();

                if(count($check_review) > 0){
                    foreach($check_review as $key => $value){
                        $review_data[$key] = new RestaurantReviewResource($value);
                    }

                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Review listed successfully!','data'=>$review_data]), 200);
                }
                else{
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'No review found!','data'=>'']), 404);
                }
            }
            else{
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Restaurant not found!','data'=>'']), 404);
            }
        }
    }

    public function addReview(Request $request){
        $validator = Validator::make($request->all(), [
            'restaurant_id' => 'required',
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

                    $check_review = RestaurantReview::where('user_id', Auth::id())->where('restaurant_id', $request->restaurant_id)->first();

                    if($check_review != null){
                        $delete_review = RestaurantReview::where('user_id', Auth::id())->where('restaurant_id', $request->restaurant_id)->update([
                            'deleted_at' => date('Y-m-d H:i:s')
                        ]);

                        $delete_photo = RestaurantReviewPhotos::where('review_id', $check_review['id'])->update([
                            'deleted_at' => date('Y-m-d H:i:s')
                        ]);
                    }
                    $review = [
                        'user_id' => Auth::id(),
                        'restaurant_id' => $request->restaurant_id,
                        'rating' => $request->rating,
                        'review' => $request->review,
                    ];

                    $new_review = RestaurantReview::create($review);

                    $review_images = $request->file('review_image');
                    
                    if($review_images != null){
                        $this->storeReviewImages($new_review->id, $check_resto['id'], $review_images);
                    }

                    $get_review = RestaurantReview::where('id', $new_review->id)->first();
                    $review_data = new RestaurantReviewResource($get_review);
                    
                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Add Review Success!','data'=> $review_data]), 200);
                }
            }
            else{
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Restaurant not found!','data'=>'']), 404);
            }
        }
    }

    public function addNewMenu(Request $request){
        $validator = Validator::make($request->all(), [
            'restaurant_id' => 'required',
            'menu_name' => 'required',
            ['menu_image' => 'required|image|mimes:png,jpg|max:53000']
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }
        else{
            $check_resto = RestaurantData::where('id', $request->restaurant_id)->first();

            if($check_resto){
                if($check_resto['user_id'] != Auth::id()){
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized!','data'=>'']), 403);
                }
                else{
                    $menu_name = $request->menu_name;
                    $menu_images = $request->file('menu_image');

                    return($this->addMenu($check_resto['id'], $menu_name, $menu_images));
                }
            }
            else{
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Restaurant not found!','data'=>'']), 404);
            }
        }
    }

    public function addNewPhotos(Request $request){
        $validator = Validator::make($request->all(), [
            'restaurant_id' => 'required',
            ['restaurant_image' => 'required|image|mimes:png,jpg|max:5300']
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }
        else{
            $check_resto = RestaurantData::where('id', $request->restaurant_id)->first();

            if($check_resto){
                if($check_resto['user_id'] != Auth::id()){
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized!','data'=>'']), 403);
                }
                else{
                    $restaurant_images = $request->file('restaurant_image');

                    $get_index = RestaurantPhotos::where('restaurant_id', $request->restaurant_id)->count();

                    return($this->addRestaurantImages($check_resto['id'], $restaurant_images, ($get_index + 1)));
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
                $menu_name = str_replace(array( '\'', '"',',' , ';', '<', '>', '?', '*', '|', ':'), '_', $menu_name);
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

            $menu_data = new RestaurantMenuResource($new_menu);

            return response()->json(new ValueMessage(['value'=>1,'message'=>'Add Menu Success!','data'=> $menu_data]), 200);
        }
        else{
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Restaurant not found!','data'=>'']), 404);
        }
    }

    public function addRestaurantImages($restaurant_id, $files, $index = null){
        $restaurant = RestaurantData::where('id', $restaurant_id)->first();
        $list_photo = [];

        if($restaurant){
            $num = $index ?? 1;

            foreach($files as $file){
                $cleanname = str_replace(array( '\'', '"',',' , ';', '<', '>', '?', '*', '|', ':'), '', $restaurant['name']);
                $fileName = str_replace(' ','-', $restaurant['id'].'_'.$cleanname.'_'.$num);
                $guessExtension = $file->guessExtension();
                $store = Storage::disk('public')->putFileAs('restaurant/image/data/'.$restaurant['id'].'/gallery', $file ,$fileName.'.'.$guessExtension);

                $new_image = [
                    'restaurant_id' => $restaurant_id,
                    'filename' => $fileName,
                    'photo_url' => 'http://hainaservice.com/storage/'.$store
                ];

                $restaurant_images = RestaurantPhotos::create($new_image);

                array_push($list_photo, $new_image);

                $num += 1; 
            }
                return response()->json(new ValueMessage(['value'=>1,'message'=>'New photo(s) added!','data'=>$list_photo]), 200);
            
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

    public function deleteMenu(Request $request){
        $validator = Validator::make($request->all(), [
            'menu_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }
        else{
            $check_menu = RestaurantMenu::where('id', $request->menu_id)->where('deleted_at', null)->first();
            
            if($check_menu){
                $check_owner = RestaurantData::where('id', $check_menu['restaurant_id'])->first();

                if($check_owner['user_id'] == Auth::id()){
                    $remove_menu_images = RestaurantMenuPhotos::where('menu_id', $request->menu_id)->update([
                        'deleted_at' => date('Y-m-d H:i:s')
                    ]);

                    $remove_menu = RestaurantMenu::where('id', $request->menu_id)->update([
                        'deleted_at' => date('Y-m-d H:i:s')
                    ]);

                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Menu removed successfully!','data'=>$check_menu]), 404);
                }
                else{
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized!','data'=>'']), 404);
                }
            }
            else{
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Menu not found!','data'=>'']), 404);
            }
        }
    }

    public function deletePhoto(Request $request){
        $validator = Validator::make($request->all(), [
            'photo_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }
        else{
            $check_photo = RestaurantPhotos::where('id', $request->photo_id)->where('deleted_at', null)->first();
            
            if($check_photo){
                $check_owner = RestaurantData::where('id', $check_menu['restaurant_id'])->first();

                if($check_owner['user_id'] == Auth::id()){
                    $remove_images = RestaurantPhotos::where('id', $request->photo_id)->update([
                        'deleted_at' => date('Y-m-d H:i:s')
                    ]);

                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Photo removed successfully!','data'=>$check_photo]), 404);
                }
                else{
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized!','data'=>'']), 404);
                }
            }
            else{
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Photo not found!','data'=>'']), 404);
            }
        }
    }

    public function deleteReview(Request $request){
        $validator = Validator::make($request->all(), [
            'review_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }
        else{
            $check_review = RestaurantReview::where('id', $request->review_id)->first();

            if($check_review != null){
                if($check_review[user_id] == Auth::id()){
                    $delete_review = RestaurantReview::where('id', $request->review_id)->update([
                        'deleted_at' => date('Y-m-d H:i:s')
                    ]);
    
                    $delete_photo = RestaurantReviewPhotos::where('review_id', $request->review_id)->update([
                        'deleted_at' => date('Y-m-d H:i:s')
                    ]);

                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Photo removed successfully!','data'=>$check_photo]), 404);
                }
                else{
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized!','data'=>'']), 403);
                }
                
            }
            else{
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Review not found!','data'=>'']), 404);
            }
        }
    }

    public function getAllCuisine(){
        $data = RestaurantCuisineType::all();

        return response()->json(new ValueMessage(['value'=>1,'message'=>'Cuisine type displayed successfully!','data'=>$data]), 200);
    }

    public function getAllType(){
        $data = RestaurantType::all();

        return response()->json(new ValueMessage(['value'=>1,'message'=>'Restaurant type displayed successfully!','data'=>$data]), 200);
    }

}