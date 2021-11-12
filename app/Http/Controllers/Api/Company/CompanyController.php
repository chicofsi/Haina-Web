<?php

namespace App\Http\Controllers\Api\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ValueMessage;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Company as CompanyResource;
use App\Models\Company;
use App\Models\CompanyAddress;

use App\Models\UserLogs;

class CompanyController extends Controller
{
    public function registerCompany(Request $request)
    {
        if($company=Company::where('id_user',$request->user()->id)->with('address','photo')->first()){
            
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Already Registered A Company!','data'=> new CompanyResource($company)]), 401);
        }else{
            $validator = Validator::make($request->all(), [
                'icon' => 'required|image',
                'name' => 'required',
                'description' => 'required',
                //'year' => 'required',
                //'staff_size' => 'required',
                'siup' => 'required',
                'id_province' => 'required'
            ]);

            if ($validator->fails()) {          
                return response()->json(['error'=>$validator->errors()], 400);                        
            }else{
                $fileName= str_replace(' ','-', $request->name.'_'.$request->user()->id.'_'.date('d-m-Y_H-i-s'));

                $guessExtension = $request->file('icon')->guessExtension();

                //store file into document folder
                $file = $request->icon->storeAs('public/company/icon',$fileName.'.'.$guessExtension);

                $company = Company::create([
                    'id_user' => $request->user()->id,
                    'name' => $request->name,
                    'icon_url' => substr($file,7),
                    'description' => $request->description,
                    'status' => 'pending review',
                    'year' => $request->year ?? 0,
                    'staff_size' => $request->staff_size ?? 1,
                    'siup' => $request->siup,
                    'id_province' => $request->id_province
                ]);

                UserLogs::create([
                   'id_user' => $request->user()->id,
                   'id_user_activity' => 9,
                   'message' => "User request to register company named ".$request->name
                ]);

                $data=Company::with('address','photo')->where('id',$company->id)->first();

                return  response()->json(new ValueMessage(['value'=>1,'message'=>'Company Register Success!','data'=>  new CompanyResource($data)]), 200);;
                
            }
        }
        

    }
    public function getCompany(Request $request)
    {
        if($company=Company::where('id_user',$request->user()->id)->with('address','photo')->first()){
            
            return response()->json(new ValueMessage(['value'=>1,'message'=>'Get Company Success!','data'=> new CompanyResource($company)]), 200);
        }else{
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Not Registered A Company Yet!','data'=> '']), 404);
        }
        

    }

    public function showCompanyList(Request $request){

        $validator = Validator::make($request->all(), [
            'keyword' => 'min:3',
            'sort_by_name' => 'in:asc,desc'
        ]);

        if ($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 400);                        
        }else{
            if($request->keyword != null){
                $company = Company::where('status', 'active')->where('name', 'like', '%'.$request->keyword.'%')->get();
            }
            else{
                $company = Company::where('status', 'active')->get();
            }
            

            if(count($company) > 0){
                $company_data = [];

                foreach($company as $key => $value){
                    //$company_data[$key] = new CompanyResource($value);

                    $company_item = new CompanyResource($value);
                    array_push($company_data, $company_item);
                }
                
                $company_list = $company_data;

                if($request->sort_by_name == "asc"){
                    $company_list = collect($company_list)->sortBy('name', SORT_NATURAL|SORT_FLAG_CASE)->toArray();
                    
                }
                else if($request->sort_by_name == "desc"){
                    $company_list = collect($company_list)->sortByDesc('name', SORT_NATURAL|SORT_FLAG_CASE)->toArray();
                }


                $total = count($company_list);
                $per_page = 10;
                $current_page = $request->page ?? 1;

                $starting_point = ($current_page * $per_page) - $per_page;

                $company_list = array_slice($company_list, $starting_point, $per_page);

                $paged_result = new \stdClass();
                $paged_result->items = $company_list;
                $paged_result->total = $total;
                $paged_result->current_page = (int)$current_page;
                $paged_result->total_page = ceil($total/$per_page);

                return response()->json(new ValueMessage(['value'=>1,'message'=>'Company displayed successfully!','data'=> $paged_result]), 200);

            }
            else{
                return response()->json(new ValueMessage(['value'=>0,'message'=>'No company found!','data'=> '']), 404);
            }
        }
        
    }
    
    
}
