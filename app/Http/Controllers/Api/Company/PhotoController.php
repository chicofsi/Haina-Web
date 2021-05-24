<?php

namespace App\Http\Controllers\Api\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ValueMessage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\Company as CompanyResource;
use App\Http\Resources\CompanyPhoto as CompanyPhotoResource;
use App\Models\Company;
use App\Models\CompanyPhoto;

use App\Models\UserLogs;

class PhotoController extends Controller
{
    
    public function registerCompanyPhoto(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'id_company' => 'required',
            'photo' => 'required|image',
            'name' => 'required',
        ]);

        if ($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 400);                        
        }else{
            if($company=Company::where('id',$request->id_company)->where('id_user',$request->user()->id)->first()){

                $fileName= str_replace(' ','-', $request->id_company.'_'.$request->name.'_'.date('d-m-Y_H-i-s'));

                $guessExtension = $request->file('photo')->guessExtension();

                //store file into document folder
                $file = Storage::disk('public')->putFileAs('company/photo/'.$request->id_company, $request->photo, $fileName.'.'.$guessExtension);
                
                $company = CompanyPhoto::create([
                    'id_company' => $request->id_company,
                    'name' => $request->name,
                    'photo_url' => $file,
                ]);

                UserLogs::create([
                   'id_user' => $request->user()->id,
                   'id_user_activity' => 14,
                   'message' => "User added company picture named ".$request->name
                ]);

                return  response()->json(new ValueMessage(['value'=>1,'message'=>'Add Company Photo Success!','data'=> new CompanyPhotoResource($company)]), 200);;
                
            }else{

                return  response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized!','data'=> ""]), 403);;
            }

           
        }
    
        

    }

    public function deleteCompanyPhoto(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if ($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 400);                        
        }else{
            if($photo=CompanyPhoto::where('id',$request->id)->first()){
                if($company=Company::where('id',$photo->id_company)->where('id_user',$request->user()->id)->first()){

                    Storage::disk('public')->delete($photo->photo_url);

                    UserLogs::create([
                       'id_user' => $request->user()->id,
                       'id_user_activity' => 15,
                       'message' => "User removed company picture named ".$photo->name
                    ]);

                    CompanyPhoto::where('id',$request->id)->delete();


                    return  response()->json(new ValueMessage(['value'=>1,'message'=>'Company Photo Successfully Deleted!','data'=> ""]), 200);
                    
                }else{
                    return  response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized!','data'=> ""]), 403);;
                }
            }else{
                return  response()->json(new ValueMessage(['value'=>0,'message'=>'Photo Not Found!','data'=> ""]), 404);;
            }
            

           
        }
    
        

    }
    
}
