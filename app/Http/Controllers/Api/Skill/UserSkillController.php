<?php

namespace App\Http\Controllers\Api\Skill;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ValueMessage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;


use App\Models\UserLogs;
use App\Models\User;
use App\Models\JobSkill;

class UserSkillController extends Controller
{

    public function getUserSkill(Request $request)
    {
        if(!$request->user()->skill->isEmpty()){
            return  response()->json(new ValueMessage(['value'=>1,'message'=>'Get User Skill Success!','data'=> $request->user()->skill]), 200);    

        }else{

            return  response()->json(new ValueMessage(['value'=>0,'message'=>'User Skill Not Found!','data'=> '']), 404);    
        } 

    }

    public function addUserSkill(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'skill_name' => 'required',
        ]);

        if ($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 400);                        
        }else{

            $user=User::where('id',$request->user()->id)->first();

            $skill=JobSkill::where('name',$request->skill_name)->first();

            if(!$skill){
                $skill=JobSkill::create(['name'=>$request->skill_name]);
            }

            $user->skill()->attach($skill);


            UserLogs::create([
                   'id_user' => $request->user()->id,
                   'id_user_activity' => 20,
                   'message' => 'User successfully added '.$request->skill_name.' skill'
                ]);


            return  response()->json(new ValueMessage(['value'=>1,'message'=>'Add User Skill Success!','data'=> '']), 200);
           
        }
    
        

    }

    public function deleteUserSkill(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'skill_name' => 'required',
        ]);

        if ($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 400);
        }else{

            $user=User::where('id',$request->user()->id)->first();

            $skill=JobSkill::where('name',$request->skill_name)->first();

            

            $user->skill()->detach($skill);


            UserLogs::create([
                   'id_user' => $request->user()->id,
                   'id_user_activity' => 21,
                   'message' => 'User removed '.$request->skill_name.' skill'
                ]);


            return  response()->json(new ValueMessage(['value'=>1,'message'=>'Remove User Skill Success!','data'=> '']), 200);
           
        }
    
        

    }

    
}
