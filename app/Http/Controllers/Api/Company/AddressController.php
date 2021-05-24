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

class AddressController extends Controller
{
    
    public function registerCompanyAddress(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'id_company' => 'required',
            'id_city' => 'required',
            'address' => 'required',
        ]);

        if ($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 400);                        
        }else{
            if($company=Company::where('id',$request->id_company)->where('id_user',$request->user()->id)->first()){
                
                 $company = CompanyAddress::create([
                    'id_company' => $request->id_company,
                    'id_city' => $request->id_city,
                    'active' => 'active',
                    'address' => $request->address,
                ]);
                 UserLogs::create([
                   'id_user' => $request->user()->id,
                   'id_user_activity' => 12,
                   'message' => "User added company address. ".$request->address
                ]);

                return  response()->json(new ValueMessage(['value'=>1,'message'=>'Add Company Address Success!','data'=> $company]), 200);;
                
            }else{

                return  response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized!','data'=> $company]), 403);;
            }

           
        }
    
        

    }

    
    
}
