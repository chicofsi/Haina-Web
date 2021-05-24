<?php

namespace App\Http\Controllers\Api\Post\Jobs\Skill;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ValueMessage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;


use App\Models\UserLogs;
use App\Models\User;
use App\Models\JobVacancy;
use App\Models\JobSkill;
use App\Models\Company;

class JobsSkillController extends Controller
{

    public function addJobsSkill(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_job_vacancy' => 'required',
            'skill_name' => 'required',
        ]);

        if ($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 400);                        
        }else{

            $job_vacancy=JobVacancy::where('id',$request->id_job_vacancy)->first();

            if($company=Company::where('id',$job_vacancy->id_company)->where('id_user',$request->user()->id)->first()){

                $skill=JobSkill::where('name',$request->skill_name)->first();

                if(!$skill){
                    $skill=JobSkill::create(['name'=>$request->skill_name]);
                }

                $job_vacancy->skill()->syncWithoutDetaching($skill);


                UserLogs::create([
                       'id_user' => $request->user()->id,
                       'id_user_activity' => 22,
                       'message' => 'User successfully added '.$request->skill_name.' skill to job vacancy titled '.$job_vacancy->title
                    ]);


                return  response()->json(new ValueMessage(['value'=>1,'message'=>'Add Jobs Skill Success!','data'=> '']), 200);
            }else{

                return  response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized!','data'=> $company]), 403);
            }

            
           
        }
    
        

    }

    public function getJobsSkill(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_job_vacancy' => 'required',
        ]);

        if ($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 400);                        
        }else{

            $job_vacancy=JobVacancy::where('id',$request->id_job_vacancy)->first();

            if($company=Company::where('id',$job_vacancy->id_company)->where('id_user',$request->user()->id)->first()){

                

                $jobvacancy_data=JobVacancy::where('id',$request->id_job_vacancy)->with('skill')->first();
                if($jobvacancy_data->skill->isEmpty()){
                    return  response()->json(new ValueMessage(['value'=>0,'message'=>'Job doesnt have skill requirement!','data'=> ""]), 404); 
                }





                return  response()->json(new ValueMessage(['value'=>1,'message'=>'Get Jobs Skill Success!','data'=> $jobvacancy_data->skill]), 200);
            }else{

                return  response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized!','data'=> $company]), 403);
            }

            
           
        }
    
        

    }

    public function removeJobsSkill(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'id_job_vacancy' => 'required',
            'skill_name' => 'required',
        ]);

        if ($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 400);                        
        }else{

            $jobvacancy=JobVacancy::where('id',$request->id_job_vacancy)->first();

            $skill=JobSkill::where('name',$request->skill_name)->first();

            if($skill==null){
                return  response()->json(new ValueMessage(['value'=>0,'message'=>'Skill Not Found!','data'=> '']), 404);
            }
            if($jobvacancy==null){
                return  response()->json(new ValueMessage(['value'=>0,'message'=>'Job Vacancy Not Found!','data'=> '']), 404);
            }

            

            $jobvacancy->skill()->detach($skill);


            return  response()->json(new ValueMessage(['value'=>1,'message'=>'Remove Job Skill Success!','data'=> '']), 200);
           
        }
    
        

    }

    

    
}
