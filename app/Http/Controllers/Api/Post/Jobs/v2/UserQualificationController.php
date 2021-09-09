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

class UserQualificationController extends Controller
{

    public function addLastEducation(Request $request){
        $validator = Validator::make($request->all(), [
            'institution' => 'required',
            'year_start' => 'lt:year_end',
            'gpa' => 'required_unless:id_edu,1',
            'major' => 'required_unless:id_edu,1',
            'id_edu' => 'required',
            'city' => 'required',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }else{
            $check_edu = UserEducation::where('id_user', Auth::id())->first();

            if(!$check_edu){
                $edu = [
                    'id_user' => Auth::id(),
                    'institution' => $request->institution,
                    'year_start' => $request->year_start,
                    'year_end' => $request->year_end,
                    'gpa' => $request->gpa ?? '',
                    'major' => $request->major ?? '',
                    'id_edu' => $request->id_edu,
                    'city' => $request->city
                ];

                $new_edu = UserEducation::create($edu);

                return response()->json(new ValueMessage(['value'=>1,'message'=>'Last education added successfully!','data'=> $new_edu]), 200);
            }
            else{
                if($request->id_edu < $check_edu['id_edu']){
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Newest education must be equal or higher level!','data'=> '']), 403);
                }
                else if($request->year_end < $check_edu['year_end']){
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Newest education must end after current education!','data'=> '']), 403);
                }
                else{
                    $update_edu = UserEducation::where('id_user', Auth::id())->update([
                        'institution' => $request->institution,
                        'year_start' => $request->year_start,
                        'year_end' => $request->year_end,
                        'gpa' => $request->gpa ?? '',
                        'major' => $request->major ?? '',
                        'id_edu' => $request->id_edu,
                        'city' => $request->city
                    ]);

                    $curr_edu = UserEducation::where('id_user', Auth::id())->first();

                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Last education updated successfully!','data'=> $curr_edu]), 200);
                }
            }
        }
    }

    public function showLastEducation(){
        $check_edu = UserEducation::where('id_user', Auth::id())->first();

        if($check_edu){
            return response()->json(new ValueMessage(['value'=>1,'message'=>'Education data listed successfully!','data'=> $check_edu]), 200);
        }
        else{
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Education data not found!','data'=> '']), 404);
        }
    }

    public function deleteLastEducation(Request $request){
        $check_edu = UserEducation::where('id_user', Auth::id())->first();

        if($check_edu){
            $delete_edu = UserEducation::where('id_user', Auth::id())->delete();

            return response()->json(new ValueMessage(['value'=>1,'message'=>'Education data deleted successfully!','data'=> $check_edu]), 200);
        }
        else{
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Education data not found!','data'=> '']), 404);
        }
    }

    public function addWorkExperience(Request $request){
        $validator = Validator::make($request->all(), [
            'company' => 'required',
            'city' => 'required',
            'date_start' => 'required|date|date:YY-MM-DD|before:date_end',
            'date_end' => 'date:YY-MM-DD',
            'position' => 'required',
            'description' => 'required',
            'salary' => 'required'
        ]);
    
        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }else{
            $check_exp = UserWorkExperience::where('id_user', Auth::id())->get();

            if($check_exp){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Work already exists!','data'=> '']), 401);
            }
            else{
                $workexp = [
                    'id_user' => Auth::id(),
                    'company' => $request->company,
                    'city' => $request->city,
                    'date_start' => $request->date_start,
                    'date_end' => $request->date_end,
                    'position' => $request->position,
                    'description' => $request->description,
                    'salary' => $request->salary
                ];
    
                $new_workexp = UserWorkExperience::create($workexp);
    
                return response()->json(new ValueMessage(['value'=>1,'message'=>'Work experience added successfully!','data'=> $new_workexp]), 200);
            }
            
        }
    }

    public function showWorkExperience(){
        $check_workexp = UserWorkExperience::where('id_user', Auth::id())->get();

        if($check_workexp){
            $workexp = collect($check_workexp)->sortByDesc('year_start')->toArray();

            return response()->json(new ValueMessage(['value'=>1,'message'=>'Work experience listed successfully!','data'=> $workexp]), 200);
        }
        else{
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Work experience data not found!','data'=> '']), 404);
        }

    }

    public function updateWorkExperience(Request $request){
        $validator = Validator::make($request->all(), [
            'date_start' => 'date|date:YY-MM-DD|before:date_end',
            'date_end' => 'date:YY-MM-DD'
        ]);
    
        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }else{
            $check_workexp = UserWorkExperience::where('id_user', Auth::id())->first();

            if($check_workexp){
                if($check_workexp['id_user'] != Auth::id()){
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized!','data'=> '']), 401);
                }
                else{
                    $update_workexp = UserWorkExperience::where('user_id', Auth::id())->update([
                        'company' => $request->company ?? $check_workexp['company'],
                        'city' => $request->city ?? $check_workexp['city'],
                        'date_start' => $request->date_start ?? $check_workexp['date_start'],
                        'date_end' => $request->date_end ?? $check_workexp['date_end'],
                        'position' => $request->position ?? $check_workexp['position'],
                        'description' => $request->description ?? $check_workexp['description'],
                        'salary' => $request->salary ?? $check_workexp['salary']
                    ]);

                    $curr_workexp = UserWorkExperience::where('id', $request->id)->first();

                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Work experience updated successfully!','data'=> $curr_workexp]), 200);
                }
            }
            else{
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Work experience data not found!','data'=> '']), 404);
            }
        }
    }

    public function deleteWorkExperience(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }else{
            $check_workexp = UserWorkExperience::where('id', $request->id)->get();

            if(!$check_workexp){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Work experience data not found!','data'=> '']), 404);
            }
            else{
                if($check_workexp['id_user'] != Auth::id()){
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized!','data'=> '']), 401);
                }
                else{
                    $delete_workexp = UserWorkExperience::where('id', $request->id)->delete();

                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Work experience deleted successfully!','data'=> $curr_workexp]), 200);
                }
            }

        }
    }

}