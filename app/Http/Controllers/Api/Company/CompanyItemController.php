<?php

namespace App\Http\Controllers\Api\Company;

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
use App\Models\Company;
use App\Models\CompanyAddress;
use App\Models\CompanyItem;
use App\Models\CompanyItemCategory;
use App\Models\CompanyItemMedia;
use App\Models\CompanyMedia;

use App\Http\Resources\Company as CompanyResource;

class CompanyItemController extends Controller
{
    public function addItemCategory(Request $request){
        $validator = Validator::make($request->all(), [
            'id_company' => 'required',
            'name' => 'required'
        ]);

        if ($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 400);                        
        }else{

            $check_company = Company::where('id', $request->id_company)->first();

            if(!$check_company){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Company not found','data'=> '']), 404);
            }
            else if($check_company && $check_company['id_user'] != Auth::id()){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized!','data'=> '']), 401);
            }
            else{
                $category = [
                    'id_company' => $request->id_company,
                    'name' => $request->name
                ];

                $new_category = CompanyItemCategory::create($category);

                return response()->json(new ValueMessage(['value'=>1,'message'=>'New Item Category Created Successfully','data'=> $new_category]), 200);

            }
            
        }
    }

    public function getAllItemCategory(){
        $get_company = Company::where('id', Auth::id())->first();

        if($get_company){
            $categories = CompanyItemCategories::where('id_company', $get_company['id'])->get();

            return response()->json(new ValueMessage(['value'=>1,'message'=>'Item Category Listed Successfully','data'=> $categories]), 200);
        }
        else{
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Company not found','data'=> '']), 404);
        }
    }

    public function addNewItem(Request $request){
        $validator = Validator::make($request->all(), [
            'id_item_category' => 'required',
            'item_name' => 'required',
            'item_description' => 'required',
            'item_price' => 'required|gte:0',
            ['item_media' => 'required|mimes:png,jpg,jpeg,gif,mp4|max:53000']
        ]);

        if ($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 400);                        
        }else{
            $check_category = CompanyItemCategory::where('id', $request->id_item_category)->first();

            if($check_category){
                $check_company = Company::where('id', $check_category['id_company'])->first();

                if($check_company['id_user'] == Auth::id()){
                    $item = [
                        'id_item_category' => $request->id_item_category,
                        'item_name' => $request->item_name,
                        'item_description' => $request->item_description,
                        'item_price' => $request->item_price
                    ];

                    $new_item = CompanyItem::create($item);

                    if($request->item_media){
                        $files = $request->file('item_media');
                        $this->storeItemMedia($new_post->id, $files);
                    }

                }
                else{
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized','data'=> '']), 401);
                }

            }
            else{
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Item category not found','data'=> '']), 404);
            }
        }
    }

    public function storeItemMedia($id, $files, $index = null){

        $item = CompanyItem::where('id', $id)->first();
        

        if(!$item){
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Item Not Found!','data'=> '']), 404);
        }
        else{
            $num = $index ?? 1;

            foreach($files as $file){
                $cleantitle = str_replace(array( '\'', '"',',' , ';', '<', '>', '?', '*', '|', ':'), '', $item['name']);
                $fileName = str_replace(' ','-', $cleantitle.'-'.$num);
                $guessExtension = $file->guessExtension();
                //dd($guessExtension);
                $store = Storage::disk('public')->putFileAs('forum/items/'.$item['id_item_category'].'/'.$id, $file ,$fileName.'.'.$guessExtension);

                $postMedia = CompanyMedia::create([
                    'id_company_item' => $item['id'],
                    'media_url' => 'http://hainaservice.com/storage/'.$store
                ]);

                $num += 1; 
            }

            $posted_media = CompanyItemMedia::where('id', $id)->get();

            return response()->json(new ValueMessage(['value'=>1,'message'=>'Post Media Success!','data'=> $posted_media]), 200);
        }
    }

}