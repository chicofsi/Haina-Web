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
use App\Models\JobVacancyLevel;
use App\Models\JobVacancyPackage;
use App\Models\JobVacancyType;
use App\Models\JobSkill;
use App\Models\User;
use App\Models\UserWorkExperience;
use App\Models\UserEducation;
use App\Models\Languages;
use App\Models\Education;
use App\Models\Payment;
use App\Models\Post;
use App\Models\UserNotification;
use App\Models\PaymentMethod;
use App\Models\PaymentMethodCategory;

use DateTime;

use App\Http\Controllers\Api\Notification\NotificationController;

class JobVacancyController extends Controller
{

    public function getVacancyData(){

        $data = new \stdClass();

        $level = JobVacancyLevel::all();

        $type = JobVacancyType::all();

        $education = Education::all();

        $skill = JobSkill::all();

        $package = JobVacancyPackage::all();

        $data->vacancy_level = $level;
        $data->vacancy_type = $type;
        $data->vacancy_education = $education;
        $data->vacancy_skill = $skill;
        $$data->vacancy_package = $package;

        return response()->json(new ValueMessage(['value'=>1,'message'=>'Data for vacancy listed successfully!','data'=> $display]), 200);

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

                if($new_vacancy->package = 1){
                    $date = new DateTime("now");
                    date_add($date, date_interval_create_from_date_string('7 days'));

                    $vacancy_update = JobVacancy::where('id', $new_vacancy->id)->update([
                        'deleted_at' => $date
                    ]);

                    $display = JobVacancy::where('id', $new_vacancy->id)->first();

                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Free vacancy created successfully!','data'=> $display]), 200);

                }
                else{
                    $package_price = JobVacancyPackage::where('id', $new_vacancy['package'])->first();

                    $new_vacancy->price = $package_price['price'];

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
                        'midtrans_id' => '',
                        'payment_method_id' => $request->payment_method_id,
                        'va_number' => $newvacancy_data->payment['virtual_account'],
                        'settlement_time' => null,
                        'payment_status' => 'pending'
                    ]);

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
            //$vacancy = JobVacancy::where('id_company', $company['id'])->where('deleted_at', null)->orWhere('deleted_at', '>', $today)->get();
            $vacancy = JobVacancy::where('id_company', $company['id'])->where('deleted_at', '!=', null)->get();

            if($vacancy){
                foreach($vacancy as $key => $value){
                    if(strtotime($value->deleted_at) < $today){
                        $value->status = "ended";
                    }
                    else{
                        $value->status = "active";
                    }

                    $value->total_applicant = count(JobVacancyApplicant::where('id_vacancy', $value->id)->get());
                    $value->shortlisted_applicant = count(JobVacancyApplicant::where('id_vacancy', $value->id)->where('status', 'shortlisted')->get());
                    $value->interview_applicant = count(JobVacancyApplicant::where('id_vacancy', $value->id)->where('status', 'interview')->get());

                    $vacancy = collect($vacancy)->sortByDesc('deleted_at')->toArray();

                }

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

    public function deleteVacancy(Request $request){
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
                    $applicant = JobVacancyApplicant::where('id_vacancy', $request->id_vacancy)->where('status', 'applied')->get();

                    if($applicant){
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
                    $applicant = JobVacancyApplicant::where('id_vacancy', $request->id_vacancy)->where('status', 'shortlisted')->get();

                    if($applicant){
                        return response()->json(new ValueMessage(['value'=>1,'message'=>'Applicant shortlist found!','data'=> $applicant]), 200);
                    }
                    else{
                        return response()->json(new ValueMessage(['value'=>0,'message'=>'No applicant found!','data'=> '']), 404);
                    }
                }
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

                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Applicant status update success!','data'=>$check_applicant]), 200);
                }
                else{
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Invalid status update!','data'=> '']), 404);
                }

                if($check_applicant['status'] == "shortlisted" && $request->status == "not accepted"){
                    $update_status = JobVacancyApplicant::where('id', $request->id_applicant)->update([
                        'status' => $request->status
                    ]);

                    $check_applicant = JobVacancyApplicant::where('id', $request->id_applicant)->first();

                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Applicant status update success!','data'=>$check_applicant]), 200);
                }
                else{
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Invalid status update!','data'=> '']), 404);
                }

                if($check_applicant['status'] == "interview" && ($request->status == "accepted" || $request->status == "not accepted")){
                    $update_status = JobVacancyApplicant::where('id', $request->id_applicant)->update([
                        'status' => $request->status
                    ]);

                    $check_applicant = JobVacancyApplicant::where('id', $request->id_applicant)->first();

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
        ]);
        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }else{
            $check_applicant = JobVacancyApplicant::where('id', $request->id_applicant)->first();

            if($check_applicant){

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
		        "order_id"            => $transaction->package.'-'.Str::random(3).'-'.$transaction->id,
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