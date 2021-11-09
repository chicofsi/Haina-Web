<?php

namespace App\Http\Controllers\Api\Company;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
use App\Models\Company;
use App\Models\CompanyAddress;
use App\Models\CompanyItem;
use App\Models\CompanyItemCategory;
use App\Models\CompanyItemMedia;
use App\Models\CompanyMedia;

use App\Http\Resources\Company as CompanyResource;
use App\Http\Resources\CompanyItemResource;

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
        $get_company = Company::where('id_user', Auth::id())->first();

        if($get_company){
            $categories = CompanyItemCategory::where('id_company', $get_company['id'])->get();

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

                    $files = $request->file('item_media');
                    $this->storeItemMedia($new_item->id, $files);
                    
                    
                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Item added successfully!','data'=>$new_item]), 200);
                    

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

    public function showCompanyItem(Request $request){
        $validator = Validator::make($request->all(), [
            'id_company' => 'required',
            'id_item_category' => 'numeric',
            'sort_by_price' => 'prohibited_if:sort_by_price|in:asc,desc',
            'sort_by_name' => 'prohibited_if:sort_by_name|in:asc,desc'
        ]);

        if ($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 400);                        
        }else{
            $result = [];
            if($request->id_item_category != null){
                $item_categories = CompanyItemCategory::where('id', $request->id_item_category)->first();

                if($item_categories){
                    $items = CompanyItem::where('id_item_category', $request->id_item_category)->get();

                        foreach($items as $key => $value){
                            $item = new CompanyItemResource($value);

                            array_push($result, $item);
                        }

                        //return response()->json(new ValueMessage(['value'=>1,'message'=>'Item list displayed successfully','data'=> $result]), 200);
                }
                else{
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Item category not found','data'=> '']), 404);
                }
            }
            else{
                $item_categories = CompanyItemCategory::where('id_company', $request->id_company)->get();

                if($item_categories){
                    foreach($item_categories as $key_category=>$value_category){
                        $items = CompanyItem::where('id_item_category', $value_category->id)->get();

                        foreach($items as $key => $value){
                            $item = new CompanyItemResource($value);

                            array_push($result, $item);
                        }
                    }

                    //return response()->json(new ValueMessage(['value'=>1,'message'=>'Item list displayed successfully','data'=> $result]), 200);
                }
                else{
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Item category not found','data'=> '']), 404);
                }
            }

            $displayed_result = $result;

            if($request->sort_by_name == "asc"){
                $displayed_result = collect($displayed_result)->sortBy('item_name')->toArray();
            }
            else if($request->sort_by_name == "desc"){
                $displayed_result = collect($displayed_result)->sortByDesc('item_name')->toArray();
            }

            if($request->sort_by_price == "asc"){
                $displayed_result = collect($displayed_result)->sortBy('item_price')->toArray();
            }
            else if($request->sort_by_price == "desc"){
                $displayed_result = collect($displayed_result)->sortByDesc('item_price')->toArray();
            }

            $total = count($displayed_result);
            $per_page = 10;
            $current_page = $request->page ?? 1;

            $starting_point = ($current_page * $per_page) - $per_page;

            $displayed_result = array_slice($displayed_result, $starting_point, $per_page);

            $paged_result = new \stdClass();
            $paged_result->items = $displayed_result;
            $paged_result->total = $total;
            $paged_result->current_page = (int)$current_page;
            $paged_result->total_page = ceil($total/$per_page);

            if(count($displayed_result) == 0){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'No items found!','data'=> '']), 404);
            }
            else{

                return response()->json(new ValueMessage(['value'=>1,'message'=>'Items displayed successfully!','data'=> $paged_result]), 200);
            }

        }
    }

    public function storeItemMedia($id, $files, $index = null){

        $item = CompanyItem::where('id', $id)->first();
        
        $num = $index ?? 1;

        foreach($files as $file){
            $cleantitle = str_replace(array( '\'', '"',',' , ';', '<', '>', '?', '*', '|', ':'), '', $item['item_name']);
            $fileName = str_replace(' ','-', $cleantitle.'-'.$num);
            $guessExtension = $file->guessExtension();
            //dd($guessExtension);
            $store = Storage::disk('public')->putFileAs('company/items/'.$item['id_item_category'].'/'.$id, $file ,$fileName.'.'.$guessExtension);

            $postMedia = CompanyItemMedia::create([
                'id_company_item' => $item['id'],
                'media_url' => 'http://hainaservice.com/storage/'.$store
            ]);

            $num += 1; 
        }

        $posted_media = CompanyItemMedia::where('id_company_item', $id)->get();

        return response()->json(new ValueMessage(['value'=>1,'message'=>'Post Media Success!','data'=> $posted_media]), 200);
    
    }

}