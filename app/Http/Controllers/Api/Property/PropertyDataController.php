<?php

namespace App\Http\Controllers\Api\Property;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use App\Models\City;
use App\Models\User;
use App\Models\Province;
use App\Models\PropertyData;
use App\Models\PropertyImageData;
use App\Models\PropertyTransaction;
use App\Models\PropertyFacility;

use App\Models\PersonalAccessToken;
use App\Http\Controllers\Api\Notification\NotificationController;

use DateTime;

use App\Http\Resources\ValueMessage;

class PropertyDataController extends Controller
{

    public function showMyProperty(){
        $property = PropertyData::where('id_user', Auth::user()->id)->with('images')->get();

        if(!$property || count($property) == 0){
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Property Not Found!','data'=> '']), 404);
        }
        else{
            foreach($property as $key=>$value){
                $facility_id = explode(',', $value->facilities);
                $property_facility = [];

                foreach($facility_id as $key_prop => $value_prop){
                    $getProp = PropertyFacility::where('id', $value_prop)->first();

                    $facility = (object) [
                        "id_facility" => $getProp['id'] ?? '0',
                        "facility_name" => $getProp['name'] ?? ' ',
                        "facility_name_zh" => $getProp['name_zh'] ?? ' '
                    ];

                    array_push($property_facility, $facility);
                }

                    $provinceid = $value->city->id_province;

                    $province = Province::where('id', $provinceid)->first();

                    $value->city->province = $province['name'];

                //dd($property_facility);
                $value->facilities = $property_facility;
            }

            return response()->json(new ValueMessage(['value'=>1,'message'=>'Property loaded successfully!','data'=> $property]), 200);
        }
    }

    public function addProperty(Request $request){
        $validator = Validator::make($request->all(), [
            'property_type' => 'in:office,warehouse,house,apartment',
            'title' => 'required',
            'condition' => 'required',
            'building_area'=> 'required',
            'bedroom' => 'required',
            'bathroom' => 'required',
            'floor_level' => 'required',
            'year' => 'required',
            //'certificate_type' => 'in:SHM,HGB,SHMSRS,Girik',
            'id_city' => 'required',
            'address' => 'required',
            'selling_price' => 'required',
            'rental_price' => 'required',
            'facilities' => 'required',
            'description' => 'required',
            //'images' => 'required'
            ['images' => 'required|image|mimes:png,jpg|max:2048']
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }
        else{
            try{

                $property = [
                    'id_user' => Auth::id(),
                    'property_type' => $request->property_type,
                    'title' => $request->title,
                    'condition' => $request->condition,
                    'building_area' => $request->building_area,
                    'land_area' => $request->land_area ?? null,
                    'bedroom' => $request->bedroom,
                    'bathroom' => $request->bathroom,
                    'floor_level' => $request->floor_level,
                    'year' => $request->year,
                    'certificate_type' => $request->certificate_type,
                    'id_city' => $request->id_city,
                    'address' => $request->address,
                    'latitude' => $request->latitude ?? '0',
                    'longitude' => $request->longitude ?? '0',
                    'selling_price' => $request->selling_price,
                    'rental_price' => $request->rental_price,
                    'facilities' => $request->facilities,
                    'post_date' => date("Y-m-d H:i:s"),
                    'description' => $request->description,
                    'status' => 'available'
                ];

                if($property['land_area'] != null && $property['building_area'] > $property['land_area'] && $property['property_type'] == "house"){
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Invalid building area','data'=> '']), 404);
                }
                else{
                    $newproperty = PropertyData::create($property);

                    $files = $request->file('images');
                    //dd($newproperty->id);
                    $this->storeImage($newproperty->id, $files);

                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Create Property Success!','data'=> $newproperty]), 200);
                }

            }
            catch(Exception $e){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Error Creating Property!','data'=> '']), 404);
            }
        }
    }

    public function storeImage($id, $files){
        

        $property = PropertyData::where('id', $id)->first();

        if(!$property){
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Property Not Found!','data'=> '']), 404);
        }
        else{
            $num = 1;

            foreach($files as $file){

                $fileName = str_replace(' ','-', $property['property_type'].'_'.$property['title'].'_'.$num);
                $guessExtension = $file->guessExtension();
                //dd($guessExtension);
                $store = Storage::disk('public')->putFileAs('property/image/'.$id, $file ,$fileName.'.'.$guessExtension);


                $property_image = PropertyImageData::create([
                    'id_property' => $id,
                    'filename' => $fileName,
                    'path' => 'http://hainaservice.com/storage/'.$store
                ]);
                //dd($property_image);
                $num += 1; 
            }

            $posted_images = PropertyImageData::where('id_property', $id)->get();

            return response()->json(new ValueMessage(['value'=>1,'message'=>'Post Image Success!','data'=> $posted_images]), 200);
        }
    }

    /*
    public function storeImage(Request $request){
        $validator = Validator::make($request->all(), [
            'id_property' => 'required',
            ['images' => 'required|image|mimes:png,jpg|max:4096']
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }
        else{
            
            $property = PropertyData::where('id', $request->id_property)->first();

            if(!$property){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Property Not Found!','data'=> '']), 404);
            }
            else{
                $num = 1;
                $files = $request->file('images');
                foreach($files as $file){

                    $fileName = str_replace(' ','-', $property['property_type'].'_'.$property['name'].'_'.$num);
                    $guessExtension = $file->guessExtension();
                    //dd($guessExtension);
                    $store = Storage::disk('public')->putFileAs('property/image/'.$request->id_property, $file ,$fileName.'.'.$guessExtension);


                    $property_image = PropertyImageData::create([
                        'id_property' => $request->id_property,
                        'filename' => $fileName,
                        'path' => $store
                    ]);
                    //dd($property_image);
                    $num += 1; 
                }

                $posted_images = PropertyImageData::where('id_property', $request->id_property)->get();

                return response()->json(new ValueMessage(['value'=>1,'message'=>'Post Image Success!','data'=> $posted_images]), 200);
            }
        }
    }
    */

    public function getPropertyDetail(Request $request){
        $validator = Validator::make($request->all(), [
            'id_property' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }
        else{
            $property = PropertyData::where('id', $request->id_property)->with('images', 'owner', 'city')->first();

            if(!$property){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Property not found!','data'=> '']), 404);
            }
            else{
                $view = $property['views'] + 1;

                $upd_view = $property->update([
                    'views' => $view
                ]);

                $facility_id = explode(',', $property->facilities);
                $property_facility = [];

                foreach($facility_id as $key_prop => $value_prop){
                    $getProp = PropertyFacility::where('id', $value_prop)->first();

                    $facility = (object) [
                        "id_facility" => $getProp['id'] ?? '0',
                        "facility_name" => $getProp['name'] ?? ' ',
                        "facility_name_zh" => $getProp['name_zh'] ?? ' '
                    ];

                    array_push($property_facility, $facility);
                }

                    $provinceid = $property->city->id_province;

                    $province = Province::where('id', $provinceid)->first();

                    $property->city->province = $province['name'];

                //dd($property_facility);
                $property->facilities = $property_facility;

                return response()->json(new ValueMessage(['value'=>1,'message'=>'Property loaded successfully!','data'=> $property]), 200);
            }
            
        }
    }

    public function showAvailableProperty(Request $request){
        $property = PropertyData::where('id_user', 'not like', Auth::user()->id)->where('status', "available")->with('images', 'owner', 'city')->get();

        if($request->property_type !== null){
            $property = $property->where('property_type', $request->property_type);
        }
        if($request->condition != null){
            $property = $property->where('condition', $request->condition);
        }
        if($request->sell_price_min != null){
            $property = $property->where('selling_price', '>=', $request->sell_price_min);
        }
        if($request->sell_price_max != null){
            $property = $property->where('selling_price', '<=', $request->sell_price_max)->where('selling_price', '>', 0);
        }
        if($request->rent_price_min != null){
            $property = $property->where('rental_price', '>=', $request->rent_price_min);
        }
        if($request->rent_price_max != null){
            $property = $property->where('rental_price', '<=', $request->rent_price_max)->where('rental_price', '>', 0);
        }


        if(!$property || count($property) == 0){
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Property Not Found!','data'=> '']), 404);
        }
        else{
            //unset($property->owner->firebase_uid);
            //unset($property->owner->email_verified_at);

            foreach($property as $key=>$value){
                $facility_id = explode(',', $value->facilities);
                $property_facility = [];

                foreach($facility_id as $key_prop => $value_prop){
                    $getProp = PropertyFacility::where('id', $value_prop)->first();

                    $facility = (object) [
                        "id_facility" => $getProp['id'] ?? '0',
                        "facility_name" => $getProp['name'] ?? ' ',
                        "facility_name_zh" => $getProp['name_zh'] ?? ' '
                    ];

                    array_push($property_facility, $facility);
                }

                    $provinceid = $value->city->id_province;

                    $province = Province::where('id', $provinceid)->first();

                    $value->city->province = $province['name'];

                //dd($property_facility);
                $value->facilities = $property_facility;
            }

            return response()->json(new ValueMessage(['value'=>1,'message'=>'Property loaded successfully!','data'=> $property]), 200);
        }
    }

    public function createTransaction(Request $request){
        $validator = Validator::make($request->all(), [
            'id_property' => 'required',
            'transaction_type' => 'required|in:buy,rental'
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }
        else{
            $property = PropertyData::where('id', $request->id_property)->first();

            if(!$property){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Property Not Found!','data'=> '']), 404);
            }
            else if($property['id_user'] == Auth::id()){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Cannot do transaction with own property!','data'=> '']), 401);
            }
            else if($property['status'] == "available"){
                if($request->transaction_type == "buy" && $property['selling_price'] == null){
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Property not for sale!','data'=> '']), 404);
                }
                else if($request->transaction_type == "rental" && $property['rental_price'] == null){
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Property not for rent!','data'=> '']), 404);
                }
                else{
                    try{

                        $property_transaction = [
                            'id_owner' => $property['id_user'],
                            'id_buyer_tenant' => Auth::id(),
                            'id_property' => $request->id_property,
                            'transaction_date' => date("Y-m-d H:i:s"),
                            'transaction_type' => $request->transaction_type,
                            'transaction_status' => "pending"
                        ];

                        $new_transaction = PropertyTransaction::create($property_transaction);

                        $property = PropertyData::where('id', $request->id_property)->update([
                            'status' => "in_transaction"
                        ]);

                        //notif
                        $token = [];
                        $usertoken = PersonalAccessToken::select('name')->where('tokenable_id', $property['id_user'])->get();
                        $buyer = User::where('id', $property['id_user'])->first();
                        $buyername = substr($buyer['fullname'], 0, strpos($buyer['fullname'], ' '));

                        foreach($usertoken as $key => $value){
                            array_push($token, $value->name); 
                        }

                        foreach ($token as $key => $value) {
                            NotificationController::sendPush($value, "Someone is interested with your property!", $buyername." did a ".$request->transaction_type." transaction with ".$property['name'], "Property", "");
                        }
                        //
        
                        return response()->json(new ValueMessage(['value'=>1,'message'=>'Create Transaction Success!','data'=> $new_transaction]), 200);
        
                    }
                    catch(Exception $e){
                        return response()->json(new ValueMessage(['value'=>0,'message'=>'Error Creating Transaction!','data'=> '']), 404);
                    }
                }   
            }
            else{
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Property Unavailable!','data'=> '']), 404);
            }
        }

    }

    public function updateTransaction(Request $request){
        $validator = Validator::make($request->all(), [
            'id_transaction' => 'required',
            'status' => 'in:done,cancel'
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }
        else{

            $transaction = PropertyTransaction::where('id', $request->id_transaction)->first();

            if(!$transaction){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Transaction Not Found!','data'=> '']), 404);
            }
            else if($transaction['transaction_status'] == "done" || $transaction['transaction_status'] == "cancel"){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Transaction Is Already Finalized/Cancelled!','data'=> '']), 401);
            }
            else{
                $update = PropertyTransaction::where('id', $request->id_transaction)->update([
                    'transaction_status' => $request->status
                ]);

                $token = [];
                $usertoken = PersonalAccessToken::select('name')->where('tokenable_id', $transaction['id_buyer_tenant'])->get();

                foreach($usertoken as $key => $value){
                    array_push($token, $value->name); 
                }

                $property = PropertyData::where('id', $transaction['id_property'])->first();

                if($request->status == "done"){
                    $property = PropertyData::where('id', $transaction['id_property'])->update([
                        'status' => $request->status
                    ]);

                    foreach ($token as $key => $value) {
                        NotificationController::sendPush($value, "Your transaction is finished", "Transaction for ".$property['name']." is being finished", "Property", "");
                    }
                }
                else if($request->status == "cancel"){
                    $property = PropertyData::where('id', $transaction['id_property'])->update([
                        'status' => 'available'
                    ]);

                    foreach ($token as $key => $value) {
                        NotificationController::sendPush($value, "Your transaction is cancelled", "Transaction for ".$property['name']." is being cancelled", "Property", "");
                    }
                }

                return response()->json(new ValueMessage(['value'=>1,'message'=>'Transaction List Successfully Updated!','data'=> $transaction]), 200);

            }
  
        }
    }

    public function showPropertyTransactionList(){
        $transaction = PropertyTransaction::where('id_buyer_tenant', Auth::id())->with('property', 'owner')->get();

        if(!$transaction || count($transaction) == 0){
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Transaction Not Found!','data'=> '']), 404);
        }
        else{
            return response()->json(new ValueMessage(['value'=>1,'message'=>'Transaction List Successfully Displayed!','data'=> $transaction]), 200);
        }

    }

    public function updatePropertyDetail(Request $request){
        $validator = Validator::make($request->all(), [
            'id_property' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }
        else{
            $property = PropertyData::where('id', $request->id_property)->first();

            if(!$property){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Property Not Found!','data'=> '']), 404);
            }
            else if($property['id_user'] != Auth::id()){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized!','data'=> '']), 401);
            }
            else if($property['status'] != "available"){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Cannot Change Property in Transaction!','data'=> '']), 401);
            }
            else{
                $update = 
                [
                    'title' => $request->title ?? $property['title'],
                    'building_area' => $request->building_area ?? $property['building_area'],
                    'land_area' => $request->land_area ?? $property['land_area'],
                    'bedroom' => $request->bedroom ?? $property['bedroom'],
                    'bathroom' => $request->bathroom ?? $property['bathroom'],
                    'certificate_type' => $request->certificate_type ?? $property['certificate_type'],
                    'selling_price' => $request->selling_price ?? $property['selling_price'],
                    'rental_price' => $request->rental_price ?? $property['rental_price'],
                    'facilities' => $request->facilities ?? $property['facilities'],
                    'description' => $request->description ?? $property['description']
                ];

                $updated_property = $property->update($update);

                return response()->json(new ValueMessage(['value'=>1,'message'=>'Property Data Updated!','data'=> $property]), 200);
            }
        }

    }

    public function showMyPropertyTransactionList(){
        $transaction = PropertyTransaction::where('id_owner', Auth::id())->with('property', 'buyer')->get();

        if(!$transaction  || count($transaction) == 0){
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Transaction Not Found!','data'=> '']), 404);
        }
        else{
            return response()->json(new ValueMessage(['value'=>1,'message'=>'Transaction List Successfully Displayed!','data'=> $transaction]), 200);
        }
    }

    public function listFacility(){
        $facilities = PropertyFacility::all();

        if(!$facilities || count($facilities) == 0){
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Facilities Not Found!','data'=> '']), 404);
        }
        else{
            return response()->json(new ValueMessage(['value'=>1,'message'=>'Facilities List Successfully Displayed!','data'=> $facilities]), 200);
        }
    }

    public function deleteProperty(Request $request){
        $property = PropertyData::where('id', $request->id_property)->first();

        if(!$property){
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Property Not Found!','data'=> '']), 404);
        }
        else if($property['id_user'] != Auth::id()){
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized!','data'=> '']), 401);
        }
        else if($property['status'] != "available"){
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Cannot Delete In Transaction Property!','data'=> '']), 404);
        }
        else{
            $property_image = PropertyImageData::where('id_property', $property['id'])->get();

            foreach($property_image as $key => $value){
                Storage::disk('public')->delete($value->path);
            }

            PropertyImageData::where('id_property', $property['id'])->delete();
            PropertyData::where('id', $property['id'])->delete();

            return response()->json(new ValueMessage(['value'=>1,'message'=>'Property Deleted!','data'=> $property]), 200);
        }

    }

}