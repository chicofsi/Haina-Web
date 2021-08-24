<?php

namespace App\Http\Controllers\Api\Post\Jobs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\ValueMessage;
use App\Http\Resources\JobVacancy as JobVacancyResource;
use App\Http\Resources\JobApplication as JobApplicationResource;
use App\Http\Resources\JobApplicant as JobApplicantResource;

use App\Http\Controllers\Api\Notification\NotificationController;

use App\Models\PersonalAccessToken;
use App\Models\JobVacancyOld;
use App\Models\JobApplicantOld;
use App\Models\UserDocs;
use App\Models\Company;
use App\Models\UserLogs;
use App\Models\User;

class JobsApplicationController extends Controller
{
    public function postJobsApplication(Request $request)
    {
		$validator = Validator::make($request->all(), [
          	'id_job_vacancy' => 'required',
            'id_user_docs' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }else{
            if($request->user()->gender==null || $request->user()->address==null ||$request->user()->birthdate==null || $request->user()->about==null){
                return  response()->json(new ValueMessage(['value'=>0,'message'=>'Please fill profile data!','data'=> '']), 403);

            }else{
                if(! $application=JobApplicant::where('id_user',$request->user()->id)->where('id_job_vacancy',$request->id_job_vacancy)->first()){
                    if($jobvacancy=JobVacancy::where('id',$request->id_job_vacancy)->first() && $userdocs=UserDocs::where('id',$request->id_user_docs)->where('id_user',$request->user()->id)){

                        $jobvacancy=JobVacancy::where('id',$request->id_job_vacancy)->with('company')->first();

                        if($jobvacancy->company->id_user==$request->user()->id){

                            return  response()->json(new ValueMessage(['value'=>0,'message'=>'Can\'t apply to this job!','data'=> '']), 403);
                        }

                        $jobapplicant = JobApplicant::create([
                            'id_user' => $request->user()->id,
                            'id_user_docs' => $request->id_user_docs,
                            'id_job_vacancy' => $request->id_job_vacancy,
                            'status' => 'pending'
                        ]);
                        
                        $jobvacancy=JobVacancy::where('id',$request->id_job_vacancy)->first();

                        UserLogs::create([
                               'id_user' => $request->user()->id,
                               'id_user_activity' => 18,
                               'message' => 'User sent job application to job vacancy titled '.$jobvacancy->title
                            ]);

                        //
                        $company_user = Company::where('id', $jobvacancy['id_company'])->first();

                        $token = [];
                        $usertoken = PersonalAccessToken::select('name')->where('tokenable_id', $company_user['id_user'])->get();

                        foreach($usertoken as $key => $value){
                            array_push($token, $value->name); 
                        }

                        foreach($token as $key => $value) {
                            NotificationController::sendPush($value, "New Job Applicant", "A new applicant for ".$jobvacancy['title'], "Job", ""); 
                        }
                        $notif_list = ['id_category' => 2, 'id_user' => $company_user['id_user'], 'title' => 'New Job Applicant', 'body' => 'A new applicant for '.$jobvacancy['title'].'.'];
                        UserNotification::create($notif_list);

                        return  response()->json(new ValueMessage(['value'=>1,'message'=>'Post Jobs Application Success!','data'=>  ""]), 200);;

                    }else{

                        return  response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized!','data'=> '']), 403);
                    }
                }
                else{

                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Applied!','data'=>  '']), 404);;
                }
            }

            
            
            
           

            
           	
        }

    	
    	

    }

    public function getMyJobApplication(Request $request)
    {

        $jobapplicant=JobApplicant::with('jobvacancy','user','userdocs')->where('id_user',$request->user()->id)->get();
        
        if($jobapplicant->isEmpty()){
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Application Doesn\'t Exist!','data'=> '']), 404);
        }else{
            foreach ($jobapplicant as $key => $value) {
                $jobdata[$key] =new JobApplicationResource($value);
            }
            return response()->json(new ValueMessage(['value'=>1,'message'=>'Get Job Application Success!','data'=> $jobdata]), 200);
        }
        
    }

    public function checkApplied(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_job' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }else{
            if($application=JobApplicant::where('id_user',$request->user()->id)->where('id_job_vacancy',$request->id_job)->first()){

                return response()->json(new ValueMessage(['value'=>0,'message'=>'Applied!','data'=>  '']), 404);;

            }else{

                $jobvacancy=JobVacancy::where('id',$request->id_job)->with('company')->first();
                if($jobvacancy->company->id_user==$request->user()->id){

                    return  response()->json(new ValueMessage(['value'=>0,'message'=>'Can\'t apply to this job!','data'=> '']), 403);
                }
                return response()->json(new ValueMessage(['value'=>1,'message'=>'Available!','data'=>  '']), 200);;
            }
        }
    }

    public function getCompanyJobApplication(Request $request)
    {
        if($company=Company::where('id_user',$request->user()->id)->with('address','photo')->first()){
            $post=JobVacancy::with('address','category','company','skill')->where('id_company',$company->id)->get();
            
            if($post->isEmpty()){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Post Doesn\'t Exist!','data'=> '']), 404);
            }else{
                $jobapplicant=JobApplicant::with('jobvacancy','user','userdocs');

                if($request->has('status')){
                    $jobapplicant=$jobapplicant->where('status',$request->status);
                }

                foreach ($post as $key => $value) {
                    $vacancyid[$key]=$value->id;
                }

                $jobapplicant=$jobapplicant->whereIn('id_job_vacancy',$vacancyid);
                $jobapplicant=$jobapplicant->get();

                

                if($jobapplicant->isEmpty()){
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Applicant Doesn\'t Exist!','data'=> '']), 404);

                }
                foreach ($jobapplicant as $key => $value) {
                    $applicant[$key]=new JobApplicantResource($value);
                }

                return response()->json(new ValueMessage(['value'=>1,'message'=>'Get Job Applicant Success!','data'=> $applicant]),200);
            }
        }else{
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Register Company First!','data'=>  '']), 404);;
        }


        
        
    }
    public function getJobApplicationStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_applicant' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }else{
            $jobapplicant=JobApplicant::with('jobvacancy','user','userdocs')->where('id',$request->id_applicant)->first();
            if(!$jobapplicant){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Applicant Doesn\'t Exist!','data'=> '']), 404);
            }else{

                $data['status']=$jobapplicant->status;
                return response()->json(new ValueMessage(['value'=>1,'message'=>'Get Job Applicant Status Success!','data'=> $data]),200);
            }

        }


        
        
    }
}
