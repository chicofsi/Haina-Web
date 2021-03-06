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
use App\Models\CompanyItemCatalog;
use App\Models\CompanyItemMedia;
use App\Models\CompanyMedia;

use App\Http\Resources\Company as CompanyResource;
use App\Http\Resources\CompanyCatalogResource;
use App\Http\Resources\CompanyItemResource;
use DateTime;

class CompanyItemController extends Controller
{
    public function addItemCatalog(Request $request){
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
                $catalog = [
                    'id_company' => $request->id_company,
                    'name' => $request->name
                ];

                $new_catalog = CompanyItemCatalog::create($catalog);

                return response()->json(new ValueMessage(['value'=>1,'message'=>'New Item Catalog Created Successfully','data'=> $new_catalog]), 200);

            }
            
        }
    }

    public function getCompanyData(Request $request){
        if($request->id_company != null){
            $company = Company::where('id', $request->id_company)->first();
        }
        else{
            $company = Company::where('id_user', Auth::id())->first();
        }

        if($company){
            $company_result = new CompanyResource($company);

            return response()->json(new ValueMessage(['value'=>1,'message'=>'Company data displayed successfully','data'=> $company_result]), 200);
        }
        else{
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Company not found','data'=> '']), 404);
        }

    }

    public function getAllItemCatalog(Request $request){
        $validator = Validator::make($request->all(), [
            'id_company' => 'required',
        ]);

        if ($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 400);                        
        }else{
            $get_company = Company::where('id', $request->id_company)->first();

            if($get_company){
                $catalogs = CompanyItemCatalog::where('id_company', $get_company['id'])->where('deleted_at', null)->get();

                foreach($catalogs as $key => $value){
                    $item_catalog[$key] = new CompanyCatalogResource($value);
                }

                return response()->json(new ValueMessage(['value'=>1,'message'=>'Item Catalog Listed Successfully','data'=> $item_catalog]), 200);
            }
            else{
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Company not found','data'=> '']), 404);
            }
        }
    }

    public function addNewItem(Request $request){
        $validator = Validator::make($request->all(), [
            'id_item_catalog' => 'required',
            'id_item_category' => 'required',
            'item_name' => 'required',
            'item_description' => 'required',
            'item_price' => 'required|gte:0',
            ['item_media' => 'required|mimes:png,jpg,jpeg,gif,mp4|max:25000']
        ]);

        if ($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 400);                        
        }else{
            $check_catalog = CompanyItemCatalog::where('id', $request->id_item_catalog)->where('deleted_at', null)->first();

            if($check_catalog){
                $check_company = Company::where('id', $check_catalog['id_company'])->first();

                if($check_company['id_user'] == Auth::id()){
                    $item = [
                        'id_item_catalog' => $request->id_item_catalog,
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
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Item catalog not found','data'=> '']), 404);
            }
        }
    }

    public function updateItem(Request $request){
        $validator = Validator::make($request->all(), [
            'id_item' => 'required',
            'id_item_category' => 'numeric',
            'id_item_catalog' => 'numeric',
            'item_price' => 'gte:0',
        ]);

        if ($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 400);                        
        }else{
            $check_item = CompanyItem::where('id', $request->id_item)->where('deleted_at', null)->first();

            if($check_item){
                $check_catalog = CompanyItemCatalog::where('id', $check_item['id_item_catalog'])->first();

                $check_company = Company::where('id', $check_catalog['id_company'])->first();

                if($check_company['id_user'] == Auth::id()){
                    $update_item = CompanyItem::where('id', $request->id_item)->update([
                        'id_item_catalog' => $request->id_item_catalog ?? $check_item['id_item_catalog'],
                        'id_item_category' => $request->id_item_category ?? $check_item['id_item_category'],
                        'item_name' => $request->item_name ?? $check_item['item_name'],
                        'item_description' => $request->item_description ?? $check_item['item_description'],
                        'item_price' => $request->item_price ?? $check_item['item_price']
                    ]);
                    
                    
                    $item = CompanyItem::where('id', $request->id_item)->first();
                    $result = new CompanyItemResource($item);

                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Items updated successfully!','data'=> $result]), 200);
                }
                else{
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized','data'=> '']), 401);
                }
            }
            else{
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Item not found!','data'=> '']), 404);
            }
        }
    }

    public function updateCatalog(Request $request){
        $validator = Validator::make($request->all(), [
            'id_item_catalog' => 'required|numeric',
            'name' => 'required'
        ]);

        if ($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 400);                        
        }else{
            $check_catalog = CompanyItemCatalog::where('id', $request->id_item_catalog)->where('deleted_at', null)->first();

            if($check_catalog){
                $check_company = Company::where('id', $check_catalog['id_company'])->first();

                if($check_company['id_user'] == Auth::id()){
                    $update_catalog = CompanyItemCatalog::where('id', $request->id_item_catalog)->update([
                        'name' => $request->name
                    ]);

                    $catalog = CompanyItemCatalog::where('id', $request->id_item_catalog)->first();

                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Catalog updated!','data'=> $catalog]), 404);
                }
                else{
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized','data'=> '']), 401);
                }
            }
            else{
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Item catalog not found!','data'=> '']), 404);
            }
        }
    }

    public function addNewItemMedia(Request $request){
        $validator = Validator::make($request->all(), [
            'id_item' => 'required',
            ['item_media' => 'required|mimes:png,jpg,jpeg,gif,mp4|max:25000']
        ]);
        if ($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 400);                        
        }else{
            $check_item = CompanyItem::where('id', $request->id_item)->where('deleted_at', null)->first();

            if($check_item){
                $check_catalog = CompanyItemCatalog::where('id', $check_item['id_item_catalog'])->first();

                $check_company = Company::where('id', $check_catalog['id_company'])->first();

                if($check_company['id_user'] == Auth::id()){
                    $index = CompanyItemMedia::where('id_item', $request->id_item)->count();

                    $files = $request->file('item_media');
                    
                    return($this->storeItemMedia($check_item['id'], $files, ($index + 1)));

                    //return response()->json(new ValueMessage(['value'=>1,'message'=>'Images added successfully!','data'=> $result]), 200);
                }
                else{
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized','data'=> '']), 401);
                }
            }
            else{
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Item not found!','data'=> '']), 404);
            }
        }
    }

    public function showCompanyItem(Request $request){
        $validator = Validator::make($request->all(), [
            'id_company' => 'required',
            'id_item_catalog' => 'numeric',
            'sort_by_price' => 'prohibited_if:sort_by_name,asc,desc|in:asc,desc',
            'sort_by_name' => 'prohibited_if:sort_by_price,asc,desc|in:asc,desc'
        ]);

        if ($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 400);                        
        }else{
            $result = [];
            if($request->id_item_catalog != null){
                $item_catalogs = CompanyItemCatalog::where('id', $request->id_item_catalog)->where('deleted_at', null)->first();

                if($item_catalogs){
                    $items = CompanyItem::where('id_item_catalog', $request->id_item_catalog)->where('deleted_at', null)->get();

                        foreach($items as $key => $value){
                            $item = new CompanyItemResource($value);

                            array_push($result, $item);
                        }

                        //return response()->json(new ValueMessage(['value'=>1,'message'=>'Item list displayed successfully','data'=> $result]), 200);
                }
                else{
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Item catalog not found','data'=> '']), 404);
                }
            }
            else{
                $item_catalogs = CompanyItemCatalog::where('id_company', $request->id_company)->where('deleted_at', null)->get();

                if($item_catalogs){
                    foreach($item_catalogs as $key_catalog=>$value_catalog){
                        $items = CompanyItem::where('id_item_catalog', $value_catalog->id)->where('deleted_at', null)->get();

                        foreach($items as $key => $value){
                            $item = new CompanyItemResource($value);

                            array_push($result, $item);
                        }
                    }

                    //return response()->json(new ValueMessage(['value'=>1,'message'=>'Item list displayed successfully','data'=> $result]), 200);
                }
                else{
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Item catalog not found','data'=> '']), 404);
                }
            }

            $displayed_result = $result;

            if($request->sort_by_name == "asc"){
                $displayed_result = collect($displayed_result)->sortBy('item_name', SORT_NATURAL|SORT_FLAG_CASE)->toArray();
            }
            else if($request->sort_by_name == "desc"){
                $displayed_result = collect($displayed_result)->sortByDesc('item_name', SORT_NATURAL|SORT_FLAG_CASE)->toArray();
            }

            if($request->sort_by_price == "asc"){
                $displayed_result = collect($displayed_result)->sortBy('item_price', SORT_NATURAL|SORT_FLAG_CASE)->toArray();
            }
            else if($request->sort_by_price == "desc"){
                $displayed_result = collect($displayed_result)->sortByDesc('item_price', SORT_NATURAL|SORT_FLAG_CASE)->toArray();
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

    public function getItemByCategory(Request $request){
        $validator = Validator::make($request->all(), [
            'id_item_category' => 'required|numeric',
            'sort_by_price' => 'prohibited_if:sort_by_name,asc,desc|prohibited_if:sort_by_time,asc,desc|in:asc,desc',
            'sort_by_name' => 'prohibited_if:sort_by_price,asc,desc|prohibited_if:sort_by_time,asc,desc|in:asc,desc',
            'sort_by_time' => 'prohibited_if:sort_by_price,asc,desc|prohibited_if:sort_by_name,asc,desc|in:asc,desc'
        ]);

        if ($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 400);                        
        }else{
            $check_category = CompanyItemCategory::where('id', $request->id_item_category)->first();

            if($check_category){
                $items = CompanyItem::where('id_item_category', $request->id_item_category)->where('deleted_at', null)->get();

                if($request->keyword != null){
                    $items = CompanyItem::where('id_item_category', $request->id_item_category)->where('item_name', 'like', '%'.$request->keyword.'%')->where('deleted_at', null)->get();
                }
                if(count($items) > 0){
                    foreach($items as $key => $value){
                        $result[$key] = new CompanyItemResource($value);
                    }

                    if($request->sort_by_name == "asc"){
                        $result = collect($result)->sortBy('item_name',SORT_NATURAL|SORT_FLAG_CASE)->toArray();
                    }
                    else if($request->sort_by_name == "desc"){
                        $result = collect($result)->sortByDesc('item_name', SORT_NATURAL|SORT_FLAG_CASE)->toArray();
                    }
    
                    if($request->sort_by_price == "asc"){
                        $result = collect($result)->sortBy('item_price', SORT_NATURAL|SORT_FLAG_CASE)->toArray();
                    }
                    else if($request->sort_by_price == "desc"){
                        $result = collect($result)->sortByDesc('item_price', SORT_NATURAL|SORT_FLAG_CASE)->toArray();
                    }

                    if($request->sort_by_time == "asc"){
                        $result = collect($result)->sortBy('created_at')->toArray();
                    }
                    else if($request->sort_by_time == "desc"){
                        $result = collect($result)->sortByDesc('created_at')->toArray();
                    }

                    $total = count($result);
                    $per_page = 10;
                    $current_page = $request->page ?? 1;

                    $starting_point = ($current_page * $per_page) - $per_page;

                    $displayed_result = array_slice($result, $starting_point, $per_page);

                    $paged_result = new \stdClass();
                    $paged_result->items = $displayed_result;
                    $paged_result->total = $total;
                    $paged_result->current_page = (int)$current_page;
                    $paged_result->total_page = ceil($total/$per_page);

                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Item listed successfully!','data'=> $paged_result]), 200);
                }
                else{
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'No items found!','data'=> '']), 404);
                }
            }
            else{
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Category not found!','data'=> '']), 404);
            }
        }
    }

    public function showItemDetail(Request $request){
        $validator = Validator::make($request->all(), [
            'id_item' => 'required|numeric',
        ]);

        if ($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 400);                        
        }else{
            $check_item = CompanyItem::where('id', $request->id_item)->where('deleted_at', null)->first();

            if($check_item){
                $item = new CompanyItemResource($check_item);

                return response()->json(new ValueMessage(['value'=>1,'message'=>'Items displayed successfully!','data'=> $item]), 200);
            }
            else{
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Item not found!','data'=> '']), 404);
            }
        }
    }

    public function deleteCatalog(Request $request){
        $validator = Validator::make($request->all(), [
            'id_item_catalog' => 'required|numeric',
        ]);

        if ($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 400);                        
        }else{
            $check_catalog = CompanyItemCatalog::where('id', $request->id_item_catalog)->where('deleted_at', null)->first();

            if($check_catalog){
                $check_item = CompanyItem::where('id_item_catalog')->where('deleted_at', null)->get();

                if(count($check_item) > 0){
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Cannot delete catalog with items in it!','data'=> '']), 401);
                }
                else{
                    $check_company = Company::where('id', $check_catalog['id_company'])->first();

                    if($check_company['id_user'] == Auth::id()){
                        $update_catalog = CompanyItemCatalog::where('id', $request->id_item_catalog)->update([
                            'deleted_at' => date('Y-m-d H:i:s')
                        ]);

                        $catalog = CompanyItemCatalog::where('id', $request->id_item_catalog)->first();

                        return response()->json(new ValueMessage(['value'=>1,'message'=>'Catalog deleted successfully','data'=> $catalog]), 401);
                    }
                    else{
                        return response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized','data'=> '']), 401);
                    }
                }
            }
            else{
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Item catalog not found!','data'=> '']), 404);
            }
        }
    }

    public function deleteItem(Request $request){
        $validator = Validator::make($request->all(), [
            'id_item' => 'required|numeric',
        ]);

        if ($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 400);                        
        }else{
            $check_item = CompanyItem::where('id', $request->id_item)->where('deleted_at', null)->first();

            if($check_item){
                $check_catalog = CompanyItemCatalog::where('id', $check_item['id_item_catalog'])->first();
                $check_company = Company::where('id', $check_catalog['id_company'])->first();

                if($check_company['id_user'] == Auth::id()){
                    $delete_media = CompanyItemMedia::where('id_item', $request->id_item)->where('deleted_at', null)->update([
                        'deleted_at' => date('Y-m-d H:i:s')
                    ]);
                    

                    $delete_item = CompanyItem::where('id', $request->id_item)->update([
                        'deleted_at' => date('Y-m-d H:i:s')
                    ]);

                    $item = CompanyItem::where('id', $request->id_item)->first();

                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Item deleted successfully','data'=> $item]), 200);
                }
                else{
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized','data'=> '']), 401);
                }
            }
            else{
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Item not found!','data'=> '']), 404);
            }
        }
    }

    public function deleteMedia(Request $request){
        $validator = Validator::make($request->all(), [
            'id_media' => 'required|numeric'
        ]);

        if ($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 400);                        
        }else{
            $check_media = CompanyItemMedia::where('id', $request->id_media)->where('deleted_at', null)->first();

            if($check_media){
                $check_item = CompanyItem::where('id', $check_media['id_item'])->first();
                $check_catalog = CompanyItemCatalog::where('id', $check_item['id_item_catalog'])->first();
                $check_company = Company::where('id', $check_catalog['id_company'])->first();

                if($check_company['id_user'] == Auth::id()){
                    $delete_media = CompanyItemMedia::where('id', $request->id_media)->update([
                        'deleted_at' => date('Y-m-d H:i:s')
                    ]);

                    $media = CompanyItemMedia::where('id', $request->id_media)->first();

                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Media deleted successfully','data'=> $media]), 200);
                }
                else{
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized','data'=> '']), 401);
                }

            }
            else{
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Media not found!','data'=> '']), 404);
            }
        }
    }

    public function searchItem(Request $request){
        $validator = Validator::make($request->all(), [
            'keyword' => 'required|min:3',
            'id_company' => 'required|numeric',
            'sort_by_price' => 'prohibited_if:sort_by_name,asc,desc|in:asc,desc',
            'sort_by_name' => 'prohibited_if:sort_by_price,asc,desc|in:asc,desc'
        ]);

        if ($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 400);                        
        }else{
            $result = [];
            $item_catalogs = CompanyItemCatalog::where('id_company', $request->id_company)->where('deleted_at', null)->get();

            if($item_catalogs){
                foreach($item_catalogs as $key_catalog=>$value_catalog){
                    $items = CompanyItem::where('id_item_catalog', $value_catalog->id)->where('item_name', 'like', '%'.$request->keyword.'%')->where('deleted_at', null)->get();

                    foreach($items as $key => $value){
                        $item = new CompanyItemResource($value);

                        array_push($result, $item);
                    }
                }

                $displayed_result = $result;

                if($request->sort_by_name == "asc"){
                    $displayed_result = collect($displayed_result)->sortBy('item_name',SORT_NATURAL|SORT_FLAG_CASE)->toArray();
                }
                else if($request->sort_by_name == "desc"){
                    $displayed_result = collect($displayed_result)->sortByDesc('item_name', SORT_NATURAL|SORT_FLAG_CASE)->toArray();
                }

                if($request->sort_by_price == "asc"){
                    $displayed_result = collect($displayed_result)->sortBy('item_price', SORT_NATURAL|SORT_FLAG_CASE)->toArray();
                }
                else if($request->sort_by_price == "desc"){
                    $displayed_result = collect($displayed_result)->sortByDesc('item_price', SORT_NATURAL|SORT_FLAG_CASE)->toArray();
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
                else{
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Item catalog not found','data'=> '']), 404);
                }
        }
    }

    public function globalSearch(Request $request){
        $validator = Validator::make($request->all(), [
            'keyword' => 'required|min:2'
        ]);

        if ($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 400);                        
        }else{
            $item_result = [];
            $company_result = [];
            $catalog_result = [];

            //company
            $companies = Company::where('status', 'active')->where('name', 'like', '%'.$request->keyword.'%')->with('category', 'address');
            if($request->company_province != null){
                $companies = $companies->where('id_province', $request->company_province);
            }
            if($request->company_category){
                $companies = $companies->whereHas('category', function($q){
                    $q->where('id', $GLOBALS['request']->company_category);
                });
            }
            $companies = $companies->get();
            foreach($companies as $key => $value){
                $company = new CompanyResource($value);

                array_push($company_result, $company);
            }

            //catalog
            $catalogs = CompanyItemCatalog::where('name', 'like', '%'.$request->keyword.'%')->where('deleted_at', null)->with('items')->get();
            foreach($catalogs as $key => $value){
                $catalog = new CompanyCatalogResource($value);

                array_push($catalog_result, $catalog);
            }

            //items
            if($request->item_category == null){
                $items = CompanyItem::where('item_name', 'like', '%'.$request->keyword.'%')->where('deleted_at', null)->get();
            }
            else{
                $items = CompanyItem::where('item_name', 'like', '%'.$request->keyword.'%')->where('id_item_category', $request->item_category)->where('deleted_at', null)->get();
            }
            foreach($items as $key => $value){
                $item = new CompanyItemResource($value);

                array_push($item_result, $item);
            }

            //sort company
            if($request->sort_company_by_name == "asc"){
                $company_result = collect($company_result)->sortBy('name',SORT_NATURAL|SORT_FLAG_CASE)->toArray();
            }
            else if($request->sort_company_by_name == "desc"){
                $company_result = collect($company_result)->sortByDesc('name',SORT_NATURAL|SORT_FLAG_CASE)->toArray();
            }

            //sort catalog
            if($request->sort_catalog_by_name == "asc"){
                $catalog_result = collect($catalog_result)->sortBy('name',SORT_NATURAL|SORT_FLAG_CASE)->toArray();
            }
            else if($request->sort_catalog_by_name == "desc"){
                $catalog_result = collect($catalog_result)->sortByDesc('name',SORT_NATURAL|SORT_FLAG_CASE)->toArray();
            }

            if($request->sort_catalog_by_price == "asc"){
                $item_result = collect($catalog_result)->sortBy('starting_price')->toArray();
            }
            else if($request->sort_catalog_by_price == "desc"){
                $item_result = collect($catalog_result)->sortByDesc('starting_price')->toArray();
            }

            //sort items
            if($request->sort_item_by_name == "asc"){
                $item_result = collect($item_result)->sortBy('item_name',SORT_NATURAL|SORT_FLAG_CASE)->toArray();
            }
            else if($request->sort_item_by_name == "desc"){
                $item_result = collect($item_result)->sortByDesc('item_name',SORT_NATURAL|SORT_FLAG_CASE)->toArray();
            }

            if($request->sort_item_by_price == "asc"){
                $item_result = collect($item_result)->sortBy('item_price')->toArray();
            }
            else if($request->sort_item_by_price == "desc"){
                $item_result = collect($item_result)->sortByDesc('item_price')->toArray();
            }

            if($request->sort_item_by_time == "asc"){
                $item_result = collect($item_result)->sortBy('created_at')->toArray();
            }
            else if($request->sort_item_by_time == "desc"){
                $item_result = collect($item_result)->sortByDesc('created_at')->toArray();
            }

            //
            $result = new \stdClass();
            $result->company = $company_result;
            $result->item = $item_result;
            $result->catalog = $catalog_result;

            return response()->json(new ValueMessage(['value'=>1,'message'=>'Search result successfully listed','data'=> $result]), 200);
        }
    }

    public function getItemCategory(){
        $categories = CompanyItemCategory::all();

        return response()->json(new ValueMessage(['value'=>1,'message'=>'Item categories displayed successfully!','data'=> $categories]), 200);
    }

    public function getPromotedItem(Request $request){
        $validator = Validator::make($request->all(), [
            'id_company' => 'required|numeric'
        ]);

        if ($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 400);                        
        }else{
            $check_company = Company::where('id', $request->id_company)->first();

            if(!$check_company){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Company not found','data'=> '']), 404);
            }
            else{
                $result = [];
                $item_catalogs = CompanyItemCatalog::where('id_company', $request->id_company)->where('deleted_at', null)->get();

                if($item_catalogs){
                    foreach($item_catalogs as $key_catalog=>$value_catalog){
                        $items = CompanyItem::where('id_item_catalog', $value_catalog->id)->where('deleted_at', null)->where('promoted', 1)->get();

                        foreach($items as $key => $value){
                            $item = new CompanyItemResource($value);

                            array_push($result, $item);
                        }
                    }

                    if(count($result) > 0){
                        return response()->json(new ValueMessage(['value'=>1,'message'=>'Promoted item displayed successfully','data'=> $result]), 200);
                    }
                    else{
                        return response()->json(new ValueMessage(['value'=>0,'message'=>'Promoted item not found!','data'=> '']), 404);
                    }
                    
                }
                else{
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Item catalog not found','data'=> '']), 404);
                }
            }
        }
    }

    public function togglePromotedItem(Request $request){
        $validator = Validator::make($request->all(), [
            'id_company' => 'required|numeric',
            'id_item' => 'required'
        ]);

        if ($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 400);                        
        }
        else{
            if ($validator->fails()) {          
                return response()->json(['error'=>$validator->errors()], 400);                        
            }
            else{
                $check_company = Company::where('id', $request->id_company)->first();
    
                if(!$check_company){
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Company not found','data'=> '']), 404);
                }
                else{
                    if(Auth::id() == $check_company['id_user']){
                        $check_item = CompanyItem::where('id', $request->id_item)->where('deleted_at', null)->first();
                        $item_catalogs = CompanyItemCatalog::where('id_company', $request->id_company)->where('deleted_at', null)->get();

                        foreach($item_catalogs as $key => $value){
                            $catalog_id[$key] = $value->id; 
                        }

                        if($check_item){
                            if(in_array($check_item['id_item_catalog'], $catalog_id)){
                                if($check_item['promoted'] == 1){
                                    $check_item = CompanyItem::where('id', $request->id_item)->update([
                                        'promoted' => 0
                                    ]);
    
                                    $updated_item = CompanyItem::where('id', $request->id_item)->first();
    
                                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Item promotion removed','data'=> $updated_item]), 200);
                                }
                                else{
                                    
                                    //foreach($item_catalogs as $key_catalog=>$value_catalog){
                                    $items = CompanyItem::whereIn('id_item_catalog', $catalog_id)->where('deleted_at', null)->where('promoted', 1)->get();
                                    $current = [];
                                    foreach($items as $key => $value){
                                        $item = $value->id;

                                        array_push($current, $item);
                                    }
                                    //}
    
                                    if(count($current) < 3){
                                        $check_item = CompanyItem::where('id', $request->id_item)->update([
                                            'promoted' => 1
                                        ]);
    
                                        $updated_item = CompanyItem::where('id', $request->id_item)->first();
    
                                        return response()->json(new ValueMessage(['value'=>1,'message'=>'New item promotion added','data'=> $updated_item]), 200);
                                    }
                                    else{
                                        return response()->json(new ValueMessage(['value'=>0,'message'=>'Maximum promoted item quota reached','data'=> '']), 404);
                                    }
                                }
                            }else{
                                return response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized!','data'=> '']), 403);
                            }
                        }
                        else{
                            return response()->json(new ValueMessage(['value'=>0,'message'=>'Item not found','data'=> '']), 404);
                        }
                    }
                    else{
                        return response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized!','data'=> '']), 403);
                    }
                }
            }
        }
    }

    public function updatePromotedItem(Request $request){
        $validator = Validator::make($request->all(), [
            'id_company' => 'required|numeric',
            //'promoted_item' => 'required'
        ]);

        if ($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 400);                        
        }else{
            if ($validator->fails()) {          
                return response()->json(['error'=>$validator->errors()], 400);                        
            }else{
                $check_company = Company::where('id', $request->id_company)->first();
    
                if(!$check_company){
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Company not found','data'=> '']), 404);
                }
                else{
                    if(Auth::id() == $check_company['id_user']){
                        $current = [];
                        $new = $request->promoted_item;

                        $result = [];

                        $item_catalogs = CompanyItemCatalog::where('id_company', $request->id_company)->where('deleted_at', null)->get();

                        if($item_catalogs){
                            foreach($item_catalogs as $key_catalog=>$value_catalog){
                                $items = CompanyItem::where('id_item_catalog', $value_catalog->id)->where('deleted_at', null)->where('promoted', 1)->get();

                                foreach($items as $key => $value){
                                    $item = $value->id;

                                    array_push($current, $item);
                                }
                            }

                            $intersect = array_intersect($new, $current);
                            
                            $new = array_values(array_diff($new, $intersect));
                            $current = array_values(array_diff($current, $intersect));

                            if(count($current) > 0){
                                foreach($current as $id){
                                    $remove_promote = CompanyItem::where('id', $id)->update([
                                        'promoted' => 0
                                    ]);
                                }
                            }

                            if(count($new) > 0){
                                foreach($new as $id){
                                    $update_promote = CompanyItem::where('id', $id)->update([
                                        'promoted' => 1
                                    ]);
                                }
                            }

                            $new_items = CompanyItem::where('id_item_catalog', $value_catalog->id)->where('deleted_at', null)->where('promoted', 1)->get();

                            if(count($new_items) > 0){
                                foreach($new_items as $key => $value){
                                    $item = new CompanyItemResource($value);
        
                                    array_push($result, $item);
                                }
                            }

                            return response()->json(new ValueMessage(['value'=>1,'message'=>'Promoted updated successfully','data'=> $result]), 200);
                        }
                        else{
                            return response()->json(new ValueMessage(['value'=>0,'message'=>'Item catalog not found','data'=> '']), 404);
                        }
                    }
                    else{
                        return response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized!','data'=> '']), 403);
                    }
                }
            }
        }
    }

    public function getCompanyItemSuggestion(Request $request){
        $validator = Validator::make($request->all(), [
            'current_item_id' => 'required'
        ]);

        if ($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 400);                        
        }
        else{
            $item = CompanyItem::where('id', $request->current_item_id)->first();

            $item_suggest = [];

            if($item){
                $item_in_catalog = CompanyItem::where('id_item_catalog', $item['id_item_catalog'])->where('id', '!=', $request->current_item_id)->where('deleted_at', null)->get();

                if(count($item_in_catalog) > 0){
                    foreach($item_in_catalog as $key => $value){
                        $item_suggest[$key] = new CompanyItemResource($value);
                    }
                }
                else{
                    $catalog = CompanyItemCatalog::where('id', $item['id_item_catalog'])->first();

                    $company = Company::where('id', $catalog['id_company'])->first();

                    $catalog_items = CompanyItemCatalog::where('id_company', $catalog['id_company'])->where('deleted_at', null)->get();

                    foreach($catalog_items as $key => $value){
                        $catalog_id[$key] = $value->id;
                    }

                    $items = CompanyItem::whereIn('id_item_catalog', $catalog_id)->where('deleted_at', null)->where('id', '!=', $request->current_item_id)->get();
                    
                    foreach($item_in_catalog as $key => $value){
                        $item_suggest[$key] = new CompanyItemResource($value);
                    }
                }

                shuffle($item_suggest);
                if(count($item_suggest) > 10){
                    $item_suggest = array_slice($item_suggest, 0, 10);
                }

                return response()->json(new ValueMessage(['value'=>1,'message'=>'Suggested items displayed successfully','data'=> $item_suggest]), 200);

            }
            else{
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Item not found','data'=> '']), 404);
            }
        }
    }

    public function getGlobalItemSuggestion(Request $request){
        $validator = Validator::make($request->all(), [
            'current_item_id' => 'required'
        ]);

        if ($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 400);                        
        }
        else{
            $item = CompanyItem::where('id', $request->current_item_id)->first();

            $item_suggest = [];

            if($item){
                $global_item = CompanyItem::where('id','!=', $request->current_item_id)->where('deleted_at', null)->get();

                if(count($global_item) > 0){

                    foreach($global_item as $key => $value){
                        $item_suggest[$key] = new CompanyItemResource($value);
                    }

                    shuffle($item_suggest);
                    if(count($item_suggest) > 10){
                        $item_suggest = array_slice($item_suggest, 0, 10);
                    }

                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Suggested items displayed successfully','data'=> $item_suggest]), 200);
                }
                else{
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Item not found','data'=> '']), 404);
                }
            }
            else{
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Item not found','data'=> '']), 404);
            }
        }
    }

    public function storeItemMedia($id, $files, $index = null){

        $item = CompanyItem::where('id', $id)->first();
        $list_media = [];
        if($item){
            $num = $index ?? 1;
            
            foreach($files as $file){
                
                $cleantitle = str_replace(array( '\'', '"',',' , ';', '<', '>', '?', '*', '|', ':'), '', $item['item_name']);
                $fileName = str_replace(' ','-', $cleantitle.'-'.$num);
                $guessExtension = $file->guessExtension();

                if($guessExtension == "mp4"){
                    $type = "video";
                }
                else{
                    $type = "image";
                }

                //dd($type);
                
                $store = Storage::disk('public')->putFileAs('company/items/'.$item['id_item_catalog'].'/'.$id, $file ,$fileName.'.'.$guessExtension);
                
                
                $postMedia = CompanyItemMedia::create([
                    'id_item' => $item['id'],
                    'media_url' => 'https://hainaservice.com/storage/'.$store,
                    'media_type' => $type
                ]);

                array_push($list_media, $postMedia);

                $num += 1; 
            }

            //$posted_media = CompanyItemMedia::where('id_item', $id)->get();

            return response()->json(new ValueMessage(['value'=>1,'message'=>'Post Media Success!','data'=> $list_media]), 200);
        }
        else{
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Item not found!','data'=> '']), 404);
        }
    
    }

}