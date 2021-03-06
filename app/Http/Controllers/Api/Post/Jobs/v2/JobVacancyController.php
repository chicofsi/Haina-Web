<?php

namespace App\Http\Controllers\Api\Post\Jobs\v2;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

use GuzzleHttp\Client;
use GuzzleHttp\TransferStats;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;

use App\Http\Resources\ValueMessage;

use App\Models\NotificationCategory;
use App\Models\PersonalAccessToken;
use App\Models\UserLogs;
use App\Models\Company;
use App\Models\CompanyMedia;
use App\Models\JobCategory;
use App\Models\JobVacancyApplicant;
use App\Models\JobVacancy;
use App\Models\JobVacancyInterview;
use App\Models\JobVacancyPayment;
use App\Models\JobVacancyLevel;
use App\Models\JobVacancyPackage;
use App\Models\JobVacancyType;
use App\Models\JobSkill;
use App\Models\User;
use App\Models\UserDocs;
use App\Models\UserWorkExperience;
use App\Models\UserEducation;
use App\Models\Languages;
use App\Models\Education;
use App\Models\Payment;
use App\Models\Post;
use App\Models\City;
use App\Models\UserNotification;
use App\Models\PaymentMethod;
use App\Models\PaymentMethodCategory;

use DateTime;
use App\Mail\InviteMail;

use App\Http\Controllers\Api\Notification\NotificationController;

class JobVacancyController extends Controller
{

    public function getVacancyData(){

        $data = new \stdClass();

        $level = JobVacancyLevel::all();

        $type = JobVacancyType::all();

        $education = Education::all();

        $skill = JobSkill::all();

        $specialist = JobCategory::all();

        foreach($specialist as $key => $value){
            $value->photo_url = "https://hainaservice.com/storage/".$value->photo_url;
        }

        $package = JobVacancyPackage::all();

        $data->vacancy_level = $level;
        $data->vacancy_type = $type;
        $data->vacancy_education = $education;
        $data->vacancy_skill = $skill;
        $data->vacancy_category = $specialist;
        $data->vacancy_package = $package;

        return response()->json(new ValueMessage(['value'=>1,'message'=>'Data for vacancy listed successfully!','data'=> $data]), 200);

    }
    
    public function createVacancy(Request $request){
        $validator = Validator::make($request->all(), [
            'id_company' => 'required',
            'position' => 'required',
            'type' => 'required',
            'level' => 'required',
            'experience' => 'required',
            'id_specialist' =>'required',
            'id_city' => 'required',
            'address' => 'required',
            'max_salary' => 'gte:min_salary',
            'salary_display' => 'required',
            'id_edu' => 'required',
            'description' => 'required',
            'package' => 'required',
            'payment_method_id' => 'required_unless:package,1',
            'skill' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }else{

            $vacancy = [
                'id_company' => $request->id_company,
                'position' => $request->position,
                'type' => $request->type,
                'level' => $request->level,
                'experience' => $request->experience,
                'id_specialist' => $request->id_specialist,
                'id_city' => $request->id_city,
                'address' => $request->address,
                'min_salary' => $request->min_salary,
                'max_salary' => $request->max_salary,
                'salary_display' => $request->salary_display,
                'id_edu' => $request->id_edu,
                'description' => $request->description,
                'package' => $request->package
            ];

            $check_company = Company::where('id', $request->id_company)->first();

            if(!$check_company){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Company not found!','data'=> '']), 404);
            }
            else if($check_company['id_user'] != Auth::id()){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized!','data'=> '']), 401);
            }
            else{
                $new_vacancy = JobVacancy::create($vacancy);

                $skill_id = explode(',', $request->skill);
                foreach($skill_id as $key => $value){
                    $vacancy = JobVacancy::where('id', $new_vacancy->id)->first();

                    $vacancy->skill()->attach($value);

                }

                if($new_vacancy->package == 1){
                    $date = new DateTime("now");
                    date_add($date, date_interval_create_from_date_string('7 days'));

                    $vacancy_update = JobVacancy::where('id', $new_vacancy->id)->update([
                        'status' => 'success',
                        'deleted_at' => $date
                    ]);

                    $display = JobVacancy::where('id', $new_vacancy->id)->first();

                    $token = [];
                    $usertoken = PersonalAccessToken::select('name')->where('tokenable_id', $check_company['id_user'])->get();

                    foreach($usertoken as $key => $value){
                        array_push($token, $value->name); 
                    }

                    NotificationController::createNotif($check_company['id_user'], "Post vacancy successful", "Your post for ".$new_vacancy['position']."ad is successful", 2, 1);
                    foreach ($token as $key => $value) {
                        NotificationController::sendPush($check_company['id_user'],$value, "Post vacancy successful", "Your post for ".$new_vacancy['position']."ad is successful", "Job", "");
                    }
                    
                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Free vacancy created successfully!','data'=> $display]), 200);

                }
                else{
                    $package_price = JobVacancyPackage::where('id', $new_vacancy['package'])->first();

                    $vacancy_update = JobVacancy::where('id', $new_vacancy->id)->update([
                        'status' => 'pending'
                    ]);

                    $new_vacancy->price = $package_price['price'];
                    $new_vacancy->order_id = $new_vacancy['package'].'-'.Str::random(3).'-'.$new_vacancy['id'];

                    $payment = PaymentMethod::where('id',$request->payment_method_id)->with('category')->first();
                    $new_vacancy['payment_data'] = json_decode($this->chargeMidtrans($new_vacancy, $payment));

                    $newvacancy_data = JobVacancy::where('id',$new_vacancy->id)->first();
                    
                    if($new_vacancy){
                        $data['payment_type'] = $new_vacancy->payment_data->payment_type;
                        $data['amount']=$new_vacancy->payment_data->gross_amount;
                        $data['payment_status']=$new_vacancy->payment_data->transaction_status;
                        foreach ($new_vacancy->payment_data->va_numbers as $key => $value) {
                            $data['virtual_account']=$value->va_number;
                            $data['bank']=$value->bank;
                        }

                        $newvacancy_data['payment'] = $data;
                    }
                    

                    $pending_payment = JobVacancyPayment::create([
                        'id_vacancy' => $new_vacancy->id,
                        'price' => $newvacancy_data->payment['amount'],
                        'order_id' => $new_vacancy->order_id,
                        'midtrans_id' => '',
                        'payment_method_id' => $request->payment_method_id,
                        'va_number' => $newvacancy_data->payment['virtual_account'],
                        'settlement_time' => null,
                        'payment_status' => 'pending'
                    ]);

                    $token = [];
                    $usertoken = PersonalAccessToken::select('name')->where('tokenable_id', $check_company['id_user'])->get();

                    foreach($usertoken as $key => $value){
                        array_push($token, $value->name); 
                    }

                    NotificationController::createNotif($check_company['id_user'], "Waiting for payment", "Your post for ".$new_vacancy['position']."ad is waiting for payment", 2, 5);
                    foreach ($token as $key => $value) {
                        NotificationController::sendPush($check_company['id_user'],$value, "Waiting for payment", "Your post for ".$new_vacancy['position']."ad is waiting for payment", "Job", "");
                    }

                    $display = JobVacancy::where('id', $new_vacancy->id)->first();

                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Paid vacancy created successfully!','data'=> $display]), 200);
                }
            }

        }
    }

    public function showVacancy(){

        $company = Company::where('id_user', Auth::id())->first();

        $today = strtotime(date("Y-m-d H:i:s"));
        //$today = new DateTime("now");

        if($company){
            //$vacancy = JobVacancy::where('id_company', $company['id'])->where('deleted_at', '!=', null)->with('skill')->get();
            $vacancy = JobVacancy::where('id_company', $company['id'])->where('status', 'not like', 'unsuccess')->with('skill')->get();

            if($vacancy){
                foreach($vacancy as $key => $value){
                    if(strtotime($value->deleted_at) < $today){
                        $value->expiry_status = "ended";
                        $value->deleted_at = date('Y-m-d\TH:i:s.u\Z' , strtotime($value->deleted_at));
                    }
                    else{
                        $value->expiry_status = "active";
                        $value->deleted_at = date('Y-m-d\TH:i:s.u\Z' , strtotime($value->deleted_at));
                    }

                    foreach($value->skill as $keyskill => $valueskill){
                        unset($valueskill->created_at);
                        unset($valueskill->updated_at);
                        unset($valueskill->pivot);
                    }

                    $company_name = Company::where('id', $value->id_company)->first();
                    $value->company_name = $company_name['name'];
                    $value->company_desc = $company_name['description'];
                
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

                    

                    $value->unprocessed_applicant = count(JobVacancyApplicant::where('id_vacancy', $value->id)->where('status', 'applied')->get());
                    $value->shortlisted_applicant = count(JobVacancyApplicant::where('id_vacancy', $value->id)->where('status', 'shortlisted')->get());
                    $value->interview_applicant = count(JobVacancyApplicant::where('id_vacancy', $value->id)->where('status', 'interview')->get());
                    $value->accepted_applicant = count(JobVacancyApplicant::where('id_vacancy', $value->id)->where('status', 'accepted')->get());

                    /*
                    $level = JobVacancyLevel::all();
                    $type = JobVacancyType::all();
                    $education = Education::all();
                    $skill = JobSkill::all();
                    $specialist = JobCategory::all();
                    $package = JobVacancyPackage::all();

                    $form_data = (object) [
                        "level" => $level,
                        "type" => $type,
                        "education" => $education,
                        "skill" => $skill,
                        "specialist" => $specialist,
                        "package" => $package
                    ];

                    $value->form_data = $form_data;
                    */

                }

                $vacancy = collect($vacancy)->sortByDesc('deleted_at')->toArray();

                return response()->json(new ValueMessage(['value'=>1,'message'=>'Show Vacancy Success!','data'=> $vacancy]), 200);
            }
            else{
                return response()->json(new ValueMessage(['value'=>0,'message'=>'No Vacancy posted!','data'=> '']), 404);
            }
        }
        else{
            return response()->json(new ValueMessage(['value'=>0,'message'=>'You do not have any company!','data'=> '']), 404);
        }
    }

    public function updateVacancy(Request $request){
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
                $check_owner = Company::where('id', $check_vacancy['id_company'])->first();

                if($check_owner['id_user'] != Auth::id()){
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized!','data'=> '']), 401);
                }
                else{
                    $minsal = $request->min_salary ?? $check_vacancy['min_salary'];
                    $maxsal = $request->max_salary ?? $check_vacancy['max_salary'];

                    if($request->min_salary != null && $request->min_salary > $maxsal){
                        return response()->json(new ValueMessage(['value'=>0,'message'=>'Min salary must be less than or equal to '.$maxsal,'data'=> '']), 401);
                    }
                    else if($request->max_salary != null && $request->max_salary < $minsal){
                        return response()->json(new ValueMessage(['value'=>0,'message'=>'Max salary must be more than or equal to '.$minsal,'data'=> '']), 401);
                    }
                    else{
                        $data_update = JobVacancy::where('id', $request->id_vacancy)->update([
                            'position' => $request->position ?? $check_vacancy['position'],
                            'type' => $request->type ?? $check_vacancy['type'],
                            'level' => $request->level ?? $check_vacancy['level'],
                            'experience' => $request->experience ?? $check_vacancy['experience'],
                            'id_specialist' => $request->id_specialist ?? $check_vacancy['id_specialist'],
                            'id_city' => $request->id_city ?? $check_vacancy['id_city'],
                            'address' => $request->address ?? $check_vacancy['address'],
                            'min_salary' => $request->min_salary ?? $check_vacancy['min_salary'],
                            'max_salary' => $request->max_salary ?? $check_vacancy['max_salary'],
                            'salary_display' => $request->salary_display ?? $check_vacancy['salary_display'],
                            'id_edu' => $request->id_edu ?? $check_vacancy['id_edu'],
                            'description' => $request->description ?? $check_vacancy['description'],
                        ]);

                        if($request->skill != null){
                            $skill_id = explode(',', $request->skill);
                            $vacancy = JobVacancy::where('id', $request->id_vacancy)->first();

                            $vacancy->skill()->sync($skill_id);

                        }
    
                        $vacancy = JobVacancy::where('id', $request->id_vacancy)->first();
    
                        return response()->json(new ValueMessage(['value'=>1,'message'=>'Update Vacancy Success!','data'=> $vacancy]), 200);
                    }

                }
            }
        }
    }

    public function deleteVacancy(Request $request){
        $validator = Validator::make($request->all(), [
            'id_vacancy' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }else{
            $check_vacancy = JobVacancy::where('id', $request->id_vacancy)->where('status','not like', 'unsuccess')->first();
            
            if(!$check_vacancy){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'No Vacancy found!','data'=> '']), 404);
            }
            else{
                $check_owner = Company::where('id', $check_vacancy['id_company'])->first();

                if($check_owner['id_user'] != Auth::id()){
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized!','data'=> '']), 401);
                }
                else {
                    if($check_vacancy['deleted_at'] == null){
                        $payment_cancel = JobVacancyPayment::where('id_vacancy', $check_vacancy['id'])->update([
                            'payment_status' => 'cancel'
                        ]);
    
                        $vacancy_delete = JobVacancy::where('id', $check_vacancy['id'])->update([
                            'deleted_at' => date('Y-m-d H:i:s')
                        ]);
    
                        return response()->json(new ValueMessage(['value'=>1,'message'=>'Vacancy Delete Success!','data'=>$check_vacancy]), 200);
                    }
                    else{
                        $currentdate = new DateTime("now");
                        $checkdate = new DateTime($check_vacancy['deleted_at']);
    
                        if($currentdate > $checkdate){
                            return response()->json(new ValueMessage(['value'=>0,'message'=>'Vacancy already deleted/expired!','data'=> '']), 403);
                        }
                        else{
                            $vacancy_delete = JobVacancy::where('id', $check_vacancy['id'])->update([
                                'deleted_at' => $currentdate
                            ]);
    
                            return response()->json(new ValueMessage(['value'=>1,'message'=>'Vacancy Delete Success!','data'=>$check_vacancy]), 200);
                        }
                    }
                }
                
            }
        }
    }

    public function showApplicant(Request $request){
        $validator = Validator::make($request->all(), [
            'id_vacancy' => 'required',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }else{
            $check_vacancy = JobVacancy::where('id', $request->id_vacancy)->where('status','not like', 'unsuccess')->first();
            
            if(!$check_vacancy){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'No Vacancy found!','data'=> '']), 404);
            }
            else{
                $check_owner = Company::where('id', $check_vacancy['id_company'])->first();

                if($check_owner['id_user'] != Auth::id()){
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized!','data'=> '']), 401);
                }
                else{
                    $applicant = JobVacancyApplicant::where('id_vacancy', $request->id_vacancy)->where('status', 'applied')->with('user.education', 'user.work_experience')->get();

                    foreach($applicant as $key => $value){
                        if($value->user->education != null){
                            $edu_name = Education::where('id', $value->user->education->id_edu)->first();

                            $value->user->education->education_level = $edu_name['name'];

                        }
                        
                    }

                    if(count($applicant) > 0){
                        return response()->json(new ValueMessage(['value'=>1,'message'=>'Applicant list found!','data'=> $applicant]), 200);
                    }
                    else{
                        return response()->json(new ValueMessage(['value'=>0,'message'=>'No applicant found!','data'=> '']), 404);
                    }
                }
            }
        }
    }

    public function showShortlist(Request $request){
        $validator = Validator::make($request->all(), [
            'id_vacancy' => 'required',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }else{
            $check_vacancy = JobVacancy::where('id', $request->id_vacancy)->where('status','not like', 'unsuccess')->first();
            
            if(!$check_vacancy){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'No Vacancy found!','data'=> '']), 404);
            }
            else{
                $check_owner = Company::where('id', $check_vacancy['id_company'])->first();

                if($check_owner['id_user'] != Auth::id()){
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized!','data'=> '']), 401);
                }
                else{
                    $applicant = JobVacancyApplicant::where('id_vacancy', $request->id_vacancy)->where('status', 'shortlisted')->with('user.education', 'user.work_experience')->get();

                    foreach($applicant as $key => $value){
                        if($value->user->education != null){
                            $edu_name = Education::where('id', $value->user->education->id_edu)->first();

                            $value->user->education->education_level = $edu_name['name'];
                        }
                    }

                    if(count($applicant) > 0){
                        return response()->json(new ValueMessage(['value'=>1,'message'=>'Applicant shortlist found!','data'=> $applicant]), 200);
                    }
                    else{
                        return response()->json(new ValueMessage(['value'=>0,'message'=>'No applicant found!','data'=> '']), 404);
                    }
                }
            }
        }
    }

    public function showInterviewList(Request $request){
        $validator = Validator::make($request->all(), [
            'id_vacancy' => 'required'
        ]);
    
        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }else{
            $check_vacancy = JobVacancy::where('id', $request->id_vacancy)->where('status','not like', 'unsuccess')->first();
            
            if(!$check_vacancy){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'No Vacancy found!','data'=> '']), 404);
            }
            else{
                $check_owner = Company::where('id', $check_vacancy['id_company'])->first();

                if($check_owner['id_user'] != Auth::id()){
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized!','data'=> '']), 401);
                }
                else{
                    $applicant = JobVacancyApplicant::where('id_vacancy', $request->id_vacancy)->where('status', 'interview')->with('user.education', 'user.work_experience')->get();

                    foreach($applicant as $key => $value){
                        if($value->user->education != null){
                            $edu_name = Education::where('id', $value->user->education->id_edu)->first();

                            $interview_schedule = JobVacancyInterview::where('id_user', $value->id_user)->where('id_vacancy', $value->id_vacancy)->first();

                            $value->interview_data = $interview_schedule;
                            $value->user->education->education_level = $edu_name['name'];
                        }
                    }

                    if(count($applicant) > 0){
                        return response()->json(new ValueMessage(['value'=>1,'message'=>'Applicant shortlist found!','data'=> $applicant]), 200);
                    }
                    else{
                        return response()->json(new ValueMessage(['value'=>0,'message'=>'No applicant found!','data'=> '']), 404);
                    }
                }
            }
        }
    }

    public function showAcceptedList(Request $request){
        $validator = Validator::make($request->all(), [
            'id_vacancy' => 'required',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }else{
            $check_vacancy = JobVacancy::where('id', $request->id_vacancy)->where('status','not like', 'unsuccess')->first();
            
            if(!$check_vacancy){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'No Vacancy found!','data'=> '']), 404);
            }
            else{
                $check_owner = Company::where('id', $check_vacancy['id_company'])->first();

                if($check_owner['id_user'] != Auth::id()){
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized!','data'=> '']), 401);
                }
                else{
                    $applicant = JobVacancyApplicant::where('id_vacancy', $request->id_vacancy)->where('status', 'accepted')->with('user.education', 'user.work_experience')->get();

                    foreach($applicant as $key => $value){
                        if($value->user->education != null){
                            $edu_name = Education::where('id', $value->user->education->id_edu)->first();

                            $value->user->education->education_level = $edu_name['name'];
                        }
                    }

                    if(count($applicant) > 0){
                        return response()->json(new ValueMessage(['value'=>1,'message'=>'Accepted applicant(s) found!','data'=> $applicant]), 200);
                    }
                    else{
                        return response()->json(new ValueMessage(['value'=>0,'message'=>'No accepted applicant found!','data'=> '']), 404);
                    }
                }
            }
        }
    }

    public function showApplicantDetail(Request $request){
        $validator = Validator::make($request->all(), [
            'id_applicant' => 'required',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }else{
            $check_applicant = JobVacancyApplicant::where('id', $request->id_applicant)->first();

            if($check_applicant){
                $vacancy = JobVacancy::where('id', $check_applicant['id_vacancy'])->first();
                $check_owner = Company::where('id', $vacancy['id_company'])->first();

                if($check_owner['id_user'] != Auth::id()){
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized!','data'=> '']), 401);
                }
                else{
                    $user_profile = User::where('id', $check_applicant['id_user'])->with('education', 'work_experience')->first();

                    $edu_name = Education::where('id', $user_profile->education->id_edu)->first();
                    $docs = UserDocs::where('id', $check_applicant['id_resume'])->get();

                    $user_profile->user_docs = $docs;
                    $user_profile->education->education_level = $edu_name['name'];

                    unset($user_profile->email_verified_at, $user_profile->created_at, $user_profile->updated_at);

                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Applicant details found!','data'=> $user_profile]), 200);
                }
            }
            else{
                return response()->json(new ValueMessage(['value'=>0,'message'=>'No applicant found!','data'=> '']), 404);
            }
        }
    }

    public function statusNotif($id_user, $id_vacancy, $status){
        $token = [];
        $usertoken = PersonalAccessToken::select('name')->where('tokenable_id', $id_user)->get();

        $vacancy_data = JobVacancy::where('id', $id_vacancy)->first();
        $company_data = Company::where('id', $vacancy_data['id_company'])->first();

        foreach($usertoken as $key => $value){
            array_push($token, $value->name); 
        }

        if($status == "accepted"){
            NotificationController::createNotif($id_user, "Application accepted", $company_data['name']." accepted your application for ".$vacancy_data['position'], 1, 1);
            foreach ($token as $key => $value) {
                NotificationController::sendPush($id_user, $value, "Application accepted", $company_data['name']." accepted your application for ".$vacancy_data['position'], "Job","");
            }
        }
        else{
            NotificationController::createNotif($id_user, "Application not accepted", $company_data['name']." decided not to accept your application for ".$vacancy_data['position'], 1, 2);
            foreach ($token as $key => $value) {
                NotificationController::sendPush($id_user, $value, "Application not accepted", $company_data['name']." decided not to accept your application for ".$vacancy_data['position'], "Job","");
            }
        }

    }

    
    public function changeApplicantStatus(Request $request){
        $validator = Validator::make($request->all(), [
            'id_applicant' => 'required',
            'status' => 'in:shortlisted,accepted,not accepted'
        ]);
    
        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }else{
            $check_applicant = JobVacancyApplicant::where('id', $request->id_applicant)->first();

            if($check_applicant){
                if($check_applicant['status'] == "applied" && ($request->status == "shortlisted" || $request->status == "not accepted")){
                    $update_status = JobVacancyApplicant::where('id', $request->id_applicant)->update([
                        'status' => $request->status
                    ]);

                    $check_applicant = JobVacancyApplicant::where('id', $request->id_applicant)->first();

                    $this->statusNotif($check_applicant['id_user'], $check_applicant['id_vacancy'], $request->status);

                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Applicant status update success!','data'=>$check_applicant]), 200);
                }
                

                else if($check_applicant['status'] == "shortlisted" && $request->status == "not accepted"){
                    $update_status = JobVacancyApplicant::where('id', $request->id_applicant)->update([
                        'status' => $request->status
                    ]);

                    $check_applicant = JobVacancyApplicant::where('id', $request->id_applicant)->first();

                    $this->statusNotif($check_applicant['id_user'], $check_applicant['id_vacancy'], $request->status);

                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Applicant status update success!','data'=>$check_applicant]), 200);
                }
                

                else if($check_applicant['status'] == "interview" && ($request->status == "accepted" || $request->status == "not accepted")){
                    $update_status = JobVacancyApplicant::where('id', $request->id_applicant)->update([
                        'status' => $request->status
                    ]);

                    $check_applicant = JobVacancyApplicant::where('id', $request->id_applicant)->first();

                    $this->statusNotif($check_applicant['id_user'], $check_applicant['id_vacancy'], $request->status);

                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Applicant status update success!','data'=>$check_applicant]), 200);
                }
                else{
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Invalid status update!','data'=> '']), 404);
                }


            }
            else{
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Applicant not found!','data'=> '']), 404);
            }
        }
    }

    public function interviewInvite(Request $request){
        $validator = Validator::make($request->all(), [
            'id_applicant' => 'required',
            'time' => 'required',
            'method' => 'in:phone,live,online',
            'duration' => 'required',
            'location' => 'required_unless:method,phone',
            'cp_name' => 'required',
            'cp_phone' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }else{
            $check_applicant = JobVacancyApplicant::where('id', $request->id_applicant)->first();

            if($check_applicant){
                if($check_applicant['status'] != "not accepted" && $check_applicant['status'] != "accepted"){
                    
                    $check_invite = JobVacancyInterview::where('id_user', $check_applicant['id_user'])->where('id_vacancy', $check_applicant['id_vacancy'])->first();

                    if(!$check_invite){
                        $update_status = JobVacancyApplicant::where('id', $request->id_applicant)->update([
                            'status' => "interview"
                        ]);
    
                        $new_invite = [
                            'id_user' => $check_applicant['id_user'],
                            'id_vacancy' => $check_applicant['id_vacancy'],
                            'time' => $request->time,
                            'method' => $request->method,
                            'duration' => $request->duration ?? 0,
                            'location' => $request->location ?? '',
                            'cp_name' => $request->cp_name ?? '',
                            'cp_phone' => $request->cp_phone ?? ''
                        ];
    
                        $interview_invite = JobVacancyInterview::create($new_invite);
    
                        $vacancy_data = JobVacancy::where('id', $check_applicant['id_vacancy'])->first();
                        $company_data = Company::where('id', $vacancy_data['id_company'])->first();
    
                        $token = [];
                        $usertoken = PersonalAccessToken::select('name')->where('tokenable_id', $check_applicant['id_user'])->get();
    
                        foreach($usertoken as $key => $value){
                            array_push($token, $value->name); 
                        }
    
                        NotificationController::createNotif($check_applicant['id_user'], "Interview Invitation", $company_data['name']." invited your for interview for ".$vacancy_data['position'], 1, 4);
                        foreach ($token as $key => $value) {
                            NotificationController::sendPush($check_applicant['id_user'], $value, "Interview Invitation", $company_data['name']." invited your for interview for ".$vacancy_data['position'], "Job","");
                        }
    
                        $user_data = User::where('id', $check_applicant['id_user'])->first();
                        //$sender_data = User::where('id', Auth::id())->first();
    
                        $objData = new \stdClass();
                        $objData->position = $vacancy_data['position'];
                        $objData->method = $new_invite['method'];
                        $objData->location = $new_invite['location'];
                        $objData->duration = $new_invite['duration'];
                        $objData->time = $new_invite['time'];
                        $objData->sender = $company_data['name'];
                        $objData->receiver = $user_data['fullname'];
                        $objData->cp_name = $new_invite['cp_name'];
                        $objData->cp_phone = $new_invite['cp_phone'];
    
                        Mail::to($user_data['email'])->send(new InviteMail($objData));
    
                        return response()->json(new ValueMessage(['value'=>1,'message'=>'Interview invite created!','data'=> $interview_invite]), 200);
                    }
                    else{
                        return response()->json(new ValueMessage(['value'=>0,'message'=>'Applicant already invited!','data'=> '']), 401);
                    }
                }
                else{
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Invalid status update!','data'=> '']), 404);
                }
            }
            else{
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Applicant not found!','data'=> '']), 404);
            }
        }
    }

    public function chargeMidtrans($transaction,$payment)
	{
		$username="SB-Mid-server-uUu-OOYw1hyxA9QH8wAbtDRl";
		$url="https://api.sandbox.midtrans.com/v2/charge";
		$data_array =  [
		    "payment_type"        => $payment->category->url,
		    "bank_transfer"       => [
		    	"bank"               => $payment->name
		    ],
            "custom_field1"        => "JobAd",
		    "transaction_details" => array(
		        "order_id"            => $transaction->order_id,
		        "gross_amount"		  => $transaction->price
		    ),
		];

		$header="Authorization: Basic ".base64_encode($username.":");
		// return json_encode($data_array)."BLABLABLAB".$header."davdavd".$username.":";
		$make_call = $this->callAPI($url, json_encode($data_array),$header);
		return $make_call;
	}

    //callAPI
    function callAPI( $url, $data, $header = false){
		$curl = curl_init();
      	curl_setopt($curl, CURLOPT_POST, 1);
      	if ($data)
      	   	curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

		// OPTIONS:
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		if(!$header){
	       	curl_setopt($curl, CURLOPT_HTTPHEADER, array(
	       	   	'Content-Type: application/json',
	       	));
	   	}else{
	   	    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
	   	       	'Content-Type: application/json',
	   	       	$header
	   	    ));
	   	}
		// EXECUTE:
		$result = curl_exec($curl);
		if(!$result){die("Connection Failure");}
		curl_close($curl);
		return $result;
	}

    

}
