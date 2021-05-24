<?php

namespace App\Http\Controllers\Api\Post\Jobs;

use App\Models\JobVacancy;
use App\Models\Post;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\ValueMessage;
use App\Http\Resources\JobVacancy as JobVacancyResource;
use App\Http\Resources\JobApplicant as JobApplicantResource;
use App\Models\Company;
use App\Http\Resources\Post as PostResource;

use App\Models\UserLogs;
use App\Models\JobApplicant;

class JobsVacancyController extends Controller
{
    public function postJobsVacancy(Request $request)
    {
		$validator = Validator::make($request->all(), [
          	'photo' => 'required|image',
            'title' => 'required',
            'id_address' => 'required',
            'id_category' => 'required',
            'description' => 'required',
            'salary_from' => 'required',
            'salary_to' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }else{
            if($company=Company::where('id_user',$request->user()->id)->with('address','photo')->first()){
                if($company->status=='active'){
                    $fileName= str_replace(' ','-', $request->id_category.'_'.$request->id_address.'_'.$request->user()->id.'_'.$request->title.'_'.date('d-m-Y_H-i-s'));

                    $guessExtension = $request->file('photo')->guessExtension();

                    //store file into document folder
                    $file = $request->photo->storeAs('public/post/picture/jobsvacancy',$fileName.'.'.$guessExtension);

                    

                    $jobVacancy = JobVacancy::create([
                        'photo_url' => substr($file,7),
                        'title' => $request->title,
                        'status' => 'pending',
                        'id_address' => $request->id_address,
                        'id_category' => $request->id_category,
                        'description' => $request->description,
                        'salary_from' => $request->salary_from,
                        'salary_to' => $request->salary_to,
                        'id_company' => $company->id,
                    ]);

                    UserLogs::create([
                       'id_user' => $request->user()->id,
                       'id_user_activity' => 16,
                       'message' => "User posted job vacancy titled ".$request->title
                    ]);

                    $data= JobVacancy::with('address', 'category', 'company','skill')->where('id',$jobVacancy->id)->first();

                    return  response()->json(new ValueMessage(['value'=>1,'message'=>'Post Jobs Success!','data'=> new JobVacancyResource($data)]), 200);;
                }else{
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Company Haven\'t Accepted!','data'=>  '']), 403);;

                }

                
            }else{
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Register Company First!','data'=>  '']), 404);;
            }
            
           	
        }

    	
    	

    }

    public function getJobVacancy(Request $request)
    {
        $post=JobVacancy::where('status','accepted')->with('address', 'category','company','skill');
        
        if($request->has('id_location')){

            $post=$post->whereHas('address', function ($q){

                $q->where('id_city', $GLOBALS['request']->id_location);
            });
        }
        if($request->has('id_category')){
            $post=$post->where('id_category', $request->id_category);
        }
        if($request->has('salary_start')){
            $post=$post->where('salary_from','>=', $request->salary_start);
        }
        
        $post=$post->orderBy('created_at','desc')->get();
        
        if($post->isEmpty()){
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Post Doesn\'t Exist!','data'=> '']), 404);
        }else{
            foreach ($post as $key => $value) {
                $postData[$key] =new JobVacancyResource($value);
            }
            return response()->json(new ValueMessage(['value'=>1,'message'=>'Get Job List Success!','data'=> $postData]), 200);
        }

    }

    public function getMyJobVacancy(Request $request)
    {

        if($company=Company::where('id_user',$request->user()->id)->with('address','photo')->first()){
            $post=JobVacancy::with('address','category','company','skill')->where('id_company',$company->id)->get();
            
            if($post->isEmpty()){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Post Doesn\'t Exist!','data'=> '']), 404);
            }else{
                foreach ($post as $key => $value) {
                    $postData[$key] =new JobVacancyResource($value);
                }
                return response()->json(new ValueMessage(['value'=>1,'message'=>'Get Job List Success!','data'=> $postData]), 200);
            }
        }else{
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Register Company First!','data'=>  '']), 404);;
        }
    }

    public function getMyJobApplicant(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_job' => 'required',
        ]);

        if ($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 400);
        }else{
            if($company=Company::where('id_user',$request->user()->id)->with('address','photo')->first()){
                $post=JobVacancy::with('address','category','jobapplicant','company','skill')->where('id_company',$company->id)->where('id',$request->id_job)->first();
                
                if(!$post){
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Post Doesn\'t Exist!','data'=> '']), 404);
                }else{
                    if($post->jobapplicant->isEmpty()){
                        return response()->json(new ValueMessage(['value'=>0,'message'=>'Applicant Doesn\'t Exist!','data'=> '']), 404);

                    }else{

                        foreach ($post->jobapplicant as $key => $value) {
                            $applicant[$key]=new JobApplicantResource($value);
                        }
                        return response()->json(new ValueMessage(['value'=>1,'message'=>'Get Job Applicant List Success!','data'=> $applicant]), 200);
                    }
                }
            }else{
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Register Company First!','data'=>  '']), 404);;
            }
        }
    }
    public function changeApplicantStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_applicant' => 'required',
            'status' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }else{
            if($company=Company::where('id_user',$request->user()->id)->with('address','photo')->first()){
                $jobapplicant=JobApplicant::where('id',$request->id_applicant)->first();

                if($jobvacancy=JobVacancy::where('id',$jobapplicant->id_job_vacancy)->where('id_company',$company->id)->first()){
                    JobApplicant::where('id',$request->id_applicant)->update(['status'=>$request->status]);
                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Job Application Status Updated!','data'=>  '']), 200);

                }else{
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized!','data'=>  '']), 403);

                }
                
            }else{
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized!','data'=>  '']), 403);
            }
            
            
        }

        
        

    }
}
