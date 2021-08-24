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

class JobVacancyController extends Controller
{
    
    public function createVacancy(Request $request){
        $validator = Validator::make($request->all(), [
            'id_company' => 'required',
            'position' => 'required',
            'type' => 'in:Full Time,Half Time,Contract,Internship',
            'level' => 'in:CEO/Director,General Manager,Manager/Assistant Manager,Supervisor,Staff',
            'experience' => 'required',
            'id_specialist' =>'required',
            'id_city' => 'required',
            'address' => 'required',
            'max_salary' => 'gte:min_salary',
            'salary_display' => 'required',
            'id_edu' => 'required',
            'description' => 'required',
            'package' => 'in:free,basic,best',
            'payment_method_id' => 'required_unless:package,free'
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

                if($new_vacancy->package == "free"){
                    $date = new DateTime("now");
                    date_add($date, date_interval_create_from_date_string('7 days'));

                    $vacancy_update = JobVacancy::where('id', $new_vacancy->id)->update([
                        'deleted_at' => $date
                    ]);

                    $display = JobVacancy::where('id', $new_vacancy->id)->first();

                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Free vacancy created successfull!','data'=> $display]), 200);

                }
                else{
                    if($new_vacancy->package == "basic"){
                        $new_vacancy->price = 50000;
                    }
                    else if($new_vacancy->package == "best"){
                        $new_vacancy->$price = 150000;
                    }

                    $payment = PaymentMethod::where('id',$request->payment_method_id)->with('category')->first();
                    $new_vacancy['payment_data'] = json_decode($this->chargeMidtrans($new_vacancy, $payment));

                    $newvacancy_data = JobVacancy::where('id',$new_vacancy->id)->first();
                    
                    $data['payment_type'] = $new_vacancy->payment_data->payment_type;
                    $data['amount']=$new_vacancy->payment_data->gross_amount;
                    $data['payment_status']=$new_vacancy->payment_data->transaction_status;
                    foreach ($new_vacancy->payment_data->va_numbers as $key => $value) {
                        $data['virtual_account']=$value->va_number;
                        $data['bank']=$value->bank;
                    }

                    $newvacancy_data['payment'] = $data;

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

                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Paid vacancy created successfull!','data'=> $display]), 200);
                }
            }

        }
    }

    public function showVacancy(){
        $company = Company::where('id_user', Auth::id())->first();

        if($company){
            $vacancy = JobVacancy::where('id_company', $company['id'])->get();

            if($vacancy){
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
		        "order_id"            => $transaction->id,
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