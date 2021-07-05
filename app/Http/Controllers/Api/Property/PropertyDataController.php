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
            'property_type' => 'in:office,warehouse,house,apartment',
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

    public function showAvailableProperty(Request $request){
        $property = PropertyData::where('id_user', 'not like', Auth::user()->id)->where('status', "available")->with('images', 'owner')->get();

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
                            'transaction_status' => "waiting"
                        ];

                        $property = PropertyData::where('id', $request->id_property)->update([
                            'status' => "in_transaction"
                        ]);
        
                        $new_transaction = PropertyTransaction::create($property_transaction);
        
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
            'status' => 'in:in_transaction,done'
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }
        else{

            $transaction = PropertyTransaction::where('id', $request->id_transaction)->first();

            if(!$transaction){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Transaction Not Found!','data'=> '']), 404);
            }
            else{
                $update = PropertyTransaction::where('id', $request->id_transaction)->update([
                    'status' => $request->status
                ]);
    
                if($request->status == "done"){
                    $property = PropertyData::where('id', $transaction['id_property'])->update([
                        'status' => $request->status
                    ]);
                }

                return response()->json(new ValueMessage(['value'=>1,'message'=>'Transaction List Successfully Updated!','data'=> $update]), 404);

            }
  
        }
    }

    public function showPropertyTransactionList(){
        $transaction = PropertyTransaction::where('id_buyer_tenant', Auth::id())->with('property', 'owner')->get();

        if(!$transaction){
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Transaction Not Found!','data'=> '']), 404);
        }
        else{
            return response()->json(new ValueMessage(['value'=>1,'message'=>'Transaction List Successfully Displayed!','data'=> $transaction]), 404);
        }

    }

    public function showMyPropertyTransactionList(){
        $transaction = PropertyTransaction::where('id_owner', Auth::id())->with('property', 'buyer')->get();

        if(!$transaction){
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Transaction Not Found!','data'=> '']), 404);
        }
        else{
            return response()->json(new ValueMessage(['value'=>1,'message'=>'Transaction List Successfully Displayed!','data'=> $transaction]), 404);
        }
    }

}