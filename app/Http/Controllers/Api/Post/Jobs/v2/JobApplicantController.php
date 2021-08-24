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
use App\Models\JobVacancyApplicant;
use App\Models\JobVacancy;
use App\Models\JobVacancyInterview;
use App\Models\JobVacancyPayment;
use App\Models\JobSkill;
use App\Models\User;
use App\Models\UserWorkExperience;
use App\Models\UserEducationDetail;
use App\Models\Languages;
use App\Models\Education;
use App\Models\Payment;
use App\Models\Post;
use App\Models\UserNotification;
use App\Models\PaymentMethod;
use App\Models\PaymentMethodCategory;

use DateTime;

use App\Http\Controllers\Api\Notification\NotificationController;

class JobApplicantController extends Controller
{
    public function applyJob(Request $request){
        $validator = Validator::make($request->all(), [
            'id_vacancy' => 'required',
            'applicant_notes' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }else{
            $check_vacancy = JobVacancy::where('id', $request->id_vacancy)->first();
            $today = new DateTime("now");

            if(!$check_vacancy || strtotime($check_vacancy['deleted_at']) < $today){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'No Vacancy found!','data'=> '']), 404);
            }
            else{
                $check_owner = Company::where('id', $check_vacancy['id_company'])->first();

                if($check_owner['id_user'] == Auth::id()){
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized: Cannot apply to own company','data'=> '']), 401);
                }
                else{
                    $applicant = [
                        'id_user' => Auth::id(),
                        'id_vacancy' => $request->id_vacancy,
                        'status' => 'applied',
                        'applicant_notes' => $request->applicant_notes,
                    ];
    
                    $new_applicant = JobVacancyApplicant::create($applicant);
    
                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Apply Job Success!','data'=>$new_applicant]), 200);
                }
            }
        }
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
                    if($checkapply['status'] == 'not accepted' || $checkapply['status'] == 'withdrawn'){
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

}