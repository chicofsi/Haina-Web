<?php

namespace App\Http\Controllers\Api\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ValueMessage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\Company as CompanyResource;
use App\Http\Resources\CompanyMedia as CompanyMediaResource;
use App\Models\Company;
use App\Models\CompanyMedia;

use App\Models\UserLogs;

class PhotoController extends Controller
{
    
    public function registerCompanyMedia(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'id_company' => 'required',
            'media' => 'required|mimes:png,jpg,gif,mp4,mov,3gp,qt|max:12000',
            'name' => 'required',
        ]);

        if ($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 400);                        
        }else{
            if($company=Company::where('id',$request->id_company)->where('id_user',$request->user()->id)->first()){

                $fileName= str_replace(' ','-', $request->id_company.'_'.$request->name.'_'.date('d-m-Y_H-i-s'));

                $guessExtension = $request->file('media')->guessExtension();

                //store file into document folder
                $file = Storage::disk('public')->putFileAs('company/media/'.$request->id_company, $request->media, $fileName.'.'.$guessExtension);
                
                $company = CompanyMedia::create([
                    'id_company' => $request->id_company,
                    'name' => $request->name,
                    'media_url' => $file,
                ]);

                UserLogs::create([
                   'id_user' => $request->user()->id,
                   'id_user_activity' => 14,
                   'message' => "User added company picture named ".$request->name
                ]);

                return  response()->json(new ValueMessage(['value'=>1,'message'=>'Add Company Media Success!','data'=> new CompanyMediaResource($company)]), 200);;
                
            }else{

                return  response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized!','data'=> ""]), 403);;
            }

           
        }
    
        

    }

    public function deleteCompanyMedia(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if ($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 400);                        
        }else{
            if($photo=CompanyMedia::where('id',$request->id)->first()){
                if($company=Company::where('id',$photo->id_company)->where('id_user',$request->user()->id)->first()){

                    Storage::disk('public')->delete($photo->media_url);

                    UserLogs::create([
                       'id_user' => $request->user()->id,
                       'id_user_activity' => 15,
                       'message' => "User removed company picture named ".$photo->name
                    ]);

                    CompanyMedia::where('id',$request->id)->delete();


                    return  response()->json(new ValueMessage(['value'=>1,'message'=>'Company Media Successfully Deleted!','data'=> ""]), 200);
                    
                }else{
                    return  response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized!','data'=> ""]), 403);;
                }
            }else{
                return  response()->json(new ValueMessage(['value'=>0,'message'=>'Media Not Found!','data'=> ""]), 404);;
            }
            

           
        }
    
        

    }
    
}
