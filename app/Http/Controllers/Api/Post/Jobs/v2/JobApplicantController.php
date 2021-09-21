<?php

namespace App\Http\Controllers\Api\Post\Jobs\v2;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

use GuzzleHttp\Client;
use GuzzleHttp\TransferStats;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;

use App\Http\Resources\ValueMessage;

use App\Models\NotificationCategory;
use App\Models\PersonalAccessToken;
use App\Models\UserLogs;
use App\Models\Company;
use App\Models\CompanyPhoto;
use App\Models\JobCategory;
use App\Models\JobVacancyApplicant;
use App\Models\JobVacancy;
use App\Models\JobVacancyBookmark;
use App\Models\JobVacancyInterview;
use App\Models\JobVacancyPayment;
use App\Models\JobVacancyLevel;
use App\Models\JobVacancyPackage;
use App\Models\JobVacancyType;
use App\Models\JobSkill;
use App\Models\User;
use App\Models\UserDocs;
use App\Models\UserWorkExperience;
use App\Models\UserEducationDetail;
use App\Models\Languages;
use App\Models\Education;
use App\Models\Payment;
use App\Models\City;
use App\Models\Post;
use App\Models\UserNotification;
use App\Models\PaymentMethod;
use App\Models\PaymentMethodCategory;

use DateTime;

use App\Http\Controllers\Api\Notification\NotificationController;

class JobApplicantController extends Controller
{
    public function showAvailableVacancy(){
        $today = date("Y-m-d H:i:s");
        $check_company = Company::where('id_user', Auth::id())->first();

        // if($check_company){
        //     $get_vacancy = JobVacancy::where('id_company', 'not_like', $check_company['id'])->get();
        // }
        // else{
            $get_vacancy = JobVacancy::where('status', 'not like', 'unsuccess')->whereDate('deleted_at', '>', $today)->get();
        //}
        
        foreach($get_vacancy as $key => $value){

            if($value->package == 3){
                $value->pinned = "Y";
            }

            $company_name = Company::where('id', $value->id_company)->with('photo')->first();
            $value->company_name = $company_name['name'];
            $value->company_desc = $company_name['description'];
            $value->company_photo = $company_name['photo'];

            foreach($value->company_photo as $keyphoto => $valuephoto){
                $valuephoto->photo_url = "https://hainaservice.com/storage/".$valuephoto->photo_url;
            }

            $value->company_url = "https://hainaservice.com/storage/".$company_name['icon_url'];

            $package_name = JobVacancyPackage::where('id', $value->package)->first();
            $value->package_name = $package_name['name'];

            $city_name = City::where('id', $value->id_city)->first();
            $value->city_name = $city_name['name'];

            $level_name = JobVacancyLevel::where('id', $value->level)->first();
            $value->level_name = $level_name['name'];

            $type_name = JobVacancyType::where('id', $value->type)->first();
            $value->type_name = $type_name['name'];

            $specialist_name = JobCategory::where('id', $value->id_specialist)->first();
            $value->specialist_name = $specialist_name['name'];

            $edu_name = Education::where('id', $value->id_edu)->first();
            $value->edu_name = $edu_name['name'];

            $bookmark_status = JobVacancyBookmark::where('id_user',Auth::id())->where('id_job_vacancy', $value->id)->first();
            if($bookmark_status != null){
                $value->bookmarked = 1;
            }
            else{
                $value->bookmarked = 0;
            }

        }

        $ordered_vacancy = collect($get_vacancy)->sortByDesc('created_at')->sortByDesc('pinned')->toArray();

        return response()->json(new ValueMessage(['value'=>1,'message'=>'Show Vacancies Success!','data'=>$ordered_vacancy]), 200);
    }

    public function applyJob(Request $request){
        $validator = Validator::make($request->all(), [
            'id_vacancy' => 'required',
            'id_resume' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }else{
            $check_vacancy = JobVacancy::where('id', $request->id_vacancy)->first();
            $today = strtotime(date("Y-m-d H:i:s"));

            if(!$check_vacancy){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'No Vacancy found!','data'=> '']), 404);
            }
            else if(strtotime($check_vacancy['deleted_at']) < $today){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Expired Vacancy Cannot be applied!','data'=> '']), 404);
            }
            else{
                $check_owner = Company::where('id', $check_vacancy['id_company'])->first();
                $check_apply = JobVacancyApplicant::where('id_user', Auth::id())->where('id_vacancy', $check_vacancy['id'])->first();

                if($check_owner['id_user'] == Auth::id()){
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized: Cannot apply to own company','data'=> '']), 401);
                }
                else if($check_apply){
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized: Already applied to this job','data'=> '']), 401);
                }
                else{
                    $applicant = [
                        'id_user' => Auth::id(),
                        'id_vacancy' => $request->id_vacancy,
                        'status' => 'applied',
                        'applicant_notes' => $request->applicant_notes ?? "",
                        'id_resume' => $request->id_resume
                    ];
    
                    $new_applicant = JobVacancyApplicant::create($applicant);

                    $token = [];
                    $usertoken = PersonalAccessToken::select('name')->where('tokenable_id', $check_owner['id_user'])->get();

                    foreach($usertoken as $key => $value){
                        array_push($token, $value->name); 
                    }

                    NotificationController::createNotif($check_owner['id_user'], "A new candidate applied!", "There is a new candidate for ".$check_vacancy['position'], 2);
                    foreach ($token as $key => $value) {
                        NotificationController::sendPush($check_owner['id_user'],$value, "A new candidate applied!", "There is a new candidate for ".$check_vacancy['position'], "Job", "");
                    }
    
                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Apply Job Success!','data'=>$new_applicant]), 200);
                }
            }
        }
    }

    public function getDocs(){
        $user = User::where('id', Auth::id())->first();

        $user_docs = UserDocs::where('id_user', $user['id'])->where('id_docs_category', 1)->orderBy('created_at')->first();

        $user_docs['docs_url'] = "http://hainaservice.com/storage/".$user_docs['docs_url'];

        return response()->json(new ValueMessage(['value'=>1,'message'=>'Get Docs Success!','data'=>$user_docs]), 200);
    }

    public function withdrawApplication(Request $request){
        $validator = Validator::make($request->all(), [
            'id_vacancy' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }else{
            $check_vacancy = JobVacancy::where('id', $request->id_vacancy)->first();

            if(!$check_vacancy){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'No Vacancy found!','data'=> '']), 404);
            }
            else{
                $check_apply = JobVacancyApplicant::where('id_user', Auth::id())->where('id_vacancy', $check_vacancy['id'])->first();

                if($check_apply){
                    if($check_apply['status'] == 'not accepted' || $check_apply['status'] == 'withdrawn'){
                        return response()->json(new ValueMessage(['value'=>0,'message'=>'Invalid action!','data'=> '']), 404);
                    }
                    else{
                        $withdraw = JobVacancyApplicant::where('id', $check_apply['id'])->update([
                            'status' => 'withdrawn'
                        ]);
    
                        return response()->json(new ValueMessage(['value'=>1,'message'=>'Withdraw Job Application Success!','data'=>$check_vacancy]), 200);
                    }
                }
                else{
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'You did not apply to this job!','data'=> '']), 404);
                }
            }
        }
    }

    public function myJobApplications (Request $request){
        $my_application = JobVacancyApplicant::where('id_user', Auth::id())->with('vacancy', 'vacancy.company')->get();

        if(count($my_application) > 0){
            //foreach($my_application as $key => $value){

            //}

            return response()->json(new ValueMessage(['value'=>1,'message'=>'Get Job Application List Success!','data'=>$my_application]), 200);
        }
        else{
            return response()->json(new ValueMessage(['value'=>0,'message'=>'No applications job found!','data'=> '']), 404);
        }
    }

    public function addVacancyBookmark(Request $request){
        $validator = Validator::make($request->all(), [
            'id_vacancy' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }else{
            $today = date("Y-m-d H:i:s");
            $check_vacancy = JobVacancy::where('id', $request->id_vacancy)->whereDate('deleted_at', '>', $today)->first();

            if(!$check_vacancy){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'No Vacancy found!','data'=> '']), 404);
            }
            else{
                $bookmark_status = JobVacancyBookmark::where('id_user',Auth::id())->where('id_job_vacancy', $request->id_vacancy)->first();
                if($bookmark_status != null){
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Job is already bookmarked!','data'=> '']), 404);
                }
                else{
                    $check_vacancy->bookmark()->attach(Auth::id());

                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Bookmark added!','data'=> ['id_vacancy' => $request->id_vacancy, 'id_user' => Auth::id()]]), 200);
                }
                
            }
        }
    }

    public function removeVacancyBookmark(Request $request){
        $validator = Validator::make($request->all(), [
            'id_vacancy' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }else{
            $today = date("Y-m-d H:i:s");
            $check_vacancy = JobVacancy::where('id', $request->id_vacancy)->whereDate('deleted_at', '>', $today)->first();

            if(!$check_vacancy){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'No Vacancy found!','data'=> '']), 404);
            }
            else{
                $bookmark_status = JobVacancyBookmark::where('id_user',Auth::id())->where('id_job_vacancy', $request->id_vacancy)->first();
                if($bookmark_status != null){
                    $check_vacancy->bookmark()->detach(Auth::id());

                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Bookmark removed!','data'=> ['id_vacancy' => $request->id_vacancy, 'id_user' => Auth::id()]]), 200);
                    
                }
                else{
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'You did not bookmark this job!','data'=> '']), 404);
                }

            }
        }
    }

    public function showVacancyBookmark(){
        $today = date("Y-m-d H:i:s");
        $get_vacancy = JobVacancy::where('status', 'not like', 'unsuccess')->whereDate('deleted_at', '>', $today)->get();

        foreach($get_vacancy as $key => $value){
            $bookmark_status = JobVacancyBookmark::where('id_user',Auth::id())->where('id_job_vacancy', $value->id)->first();
            if($bookmark_status == null){
                unset($get_vacancy[$key]);
            }
        }

        $ordered_vacancy = collect($get_vacancy)->sortByDesc('created_at')->toArray();

        return response()->json(new ValueMessage(['value'=>1,'message'=>'Show Bookmarked Vacancy Success!','data'=>$ordered_vacancy]), 200);

    }

}