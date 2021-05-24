<?php

namespace App\Http\Controllers\Api\UserDocs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ValueMessage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\UserDocs;
use App\Models\DocsCategory;
use App\Http\Resources\UserDocs as UserDocsResource;


use App\Models\UserLogs;

class UserDocsController extends Controller
{
    
    public function addUserDocs(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'id_docs_category' => 'required',
            'docs' => 'required|file',
            'name' => 'required',
        ]);

        if ($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 400);                        
        }else{

            $fileName= str_replace(' ','-', $request->name.'_'.date('d-m-Y_H-i-s'));

            $guessExtension = $request->file('docs')->guessExtension();

            //store file into document folder
            $file = Storage::disk('public')->putFileAs('user/'.$request->user()->id.'/docs', $request->docs, $fileName.'.'.$guessExtension);
            
            $userDocs = UserDocs::create([
                'id_user' => $request->user()->id,
                'id_docs_category' => $request->id_docs_category,
                'docs_name' => $request->name,
                'docs_url' => $file,
            ]);

            $docsCat=DocsCategory::where('id',$request->id_docs_category)->first()->name;


            UserLogs::create([
                   'id_user' => $request->user()->id,
                   'id_user_activity' => 7,
                   'message' => 'User successfully added '.$docsCat.' as '.$request->name
                ]);

            $docsData=UserDocs::where('id',$userDocs->id)->with('docscategory')->first();

            return  response()->json(new ValueMessage(['value'=>1,'message'=>'Add User Document Success!','data'=> new UserDocsResource($docsData)]), 200);;
            
            

           
        }
    
        

    }

    public function deleteUserDocs(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);

        if ($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 400);                        
        }else{
            if($userDocs=UserDocs::where('id',$request->id)->where('id_user',$request->user()->id)->first()){

                Storage::disk('public')->delete($userDocs->docs_url);

                $docsCat=DocsCategory::where('id',$userDocs->id_docs_category)->first()->name;

                UserLogs::create([
                   'id_user' => $request->user()->id,
                   'id_user_activity' => 8,
                   'message' => 'User deleted '.$docsCat.' named '.$userDocs->docs_name
                ]);


                UserDocs::where('id',$request->id)->delete();


                return  response()->json(new ValueMessage(['value'=>1,'message'=>'User Document Successfully Deleted!','data'=> ""]), 200);
                    
                
            }else{
                return  response()->json(new ValueMessage(['value'=>0,'message'=>'Document Not Found!','data'=> ""]), 404);;
            }
            

           
        }
    
        

    }

    public function getUserDocs(Request $request)
    {
        $userDocs=UserDocs::where('id_user',$request->user()->id);
        if($request->has('id_docs_category')){
            $userDocs=$userDocs->where('id_docs_category',$request->id_docs_category);
        }
        $userDocs=$userDocs->get();

        if($userDocs->isEmpty()){
            return response()->json(new ValueMessage(['value'=>0,'message'=>'User Documents Doesn\'t Exist!','data'=> '']), 404);

            
                
            
        }else{
            foreach ($userDocs as $key => $value) {
                $data[$key]=new UserDocsResource($value);
            }
            return response()->json(new ValueMessage(['value'=>1,'message'=>'Get User Documents Success!','data'=> $data]), 200);
        }
            

           
        
    
        

    }
    
}
