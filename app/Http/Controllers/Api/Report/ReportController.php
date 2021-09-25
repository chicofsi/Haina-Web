<?php

namespace App\Http\Controllers\Api\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use App\Models\UserReport;
use App\Models\CompanyReport;

use App\Models\User;
use App\Models\Company;
use App\Models\ForumCategory;
use App\Models\Subforum;
use App\Models\ForumBan;
use App\Models\ForumBookmark;
use App\Models\ForumPost;
use App\Models\ForumComment;
use App\Models\ForumFollowers;
use App\Models\ForumLog;
use App\Models\ForumMod;

use App\Models\ReportCategory;

use App\Models\PersonalAccessToken;

use DateTime;

use App\Http\Resources\ValueMessage;

class ReportController extends Controller
{

    public function fileReport(Request $request){

        $validator = Validator::make($request->all(), [
            'content' => 'in:post,subforum,comment,profile,company',
            'category_id' => 'required|numeric',
            'subforum_id' => 'required_if:content,subforum',
            'post_id' => 'required_if:content,post',
            'comment_id' => 'required_if:content,comment',
            'company_id' => 'required_if:content,company',
            'user_id' => 'required_if:content,profile',
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }
        else{
            //report_list_
            if($request->content == "company"){
                $check_company= Company::where('id', $request->company_id)->first();

                if(!$check_company){
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Company not found!','data'=> '']), 404);
                }
                else if($check_company['id_user'] == Auth::id()){
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized: Cannot report own company','data'=> '']), 401);
                }
                else{
                    $report_data = [
                        'id_user_reporter' => Auth::id(),
                        'id_user_reported' => $check_company['id_user'],
                        'id_report_category' => $request->category_id,
                    ];
    
                    $new_report = UserReport::create($report_data);
    
                    $new_report->company()->attach($request->company_id);
                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Company reported!','data'=> $new_report]), 200);
                    
                }
                
            }
            else if($request->content == "subforum"){
                $check_subforum = Subforum::where('id', $request->subforum_id)->first();

                if(!$check_subforum){
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Subforum not found!','data'=> '']), 404);
                }
                else if($check_subforum['creator_id'] == Auth::id()){
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized: Cannot report own subforum','data'=> '']), 401);
                }
                else{
                    $report_data = [
                        'id_user_reporter' => Auth::id(),
                        'id_user_reported' => $check_subforum['creator_id'],
                        'id_report_category' => $request->category_id,
                    ];
    
                    $new_report = UserReport::create($report_data);

                    $new_report->subforum()->attach($check_subforum['id']);

                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Subforum reported!','data'=> $new_report]), 200);
                }

            }
            else if($request->content == "post"){
                    $check_post = ForumPost::where('id', $request->post_id)->first();

                if(!$check_post){
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Post not found!','data'=> '']), 404);
                }
                else if($check_post['user_id'] == Auth::id()){
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized: Cannot report own post','data'=> '']), 401);
                }
                else{
                    $report_data = [
                        'id_user_reporter' => Auth::id(),
                        'id_user_reported' => $check_post['user_id'],
                        'id_report_category' => $request->category_id,
                    ];
    
                    $new_report = UserReport::create($report_data);

                    $new_report->post()->attach($check_post['id']);

                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Post reported!','data'=> $new_report]), 200);
                }
            }
            else if($request->content == "comment"){
                $check_comment = ForumComment::where('id', $request->comment_id)->first();

                if(!$check_comment){
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Comment not found!','data'=> '']), 404);
                }
                else if($check_comment['user_id'] == Auth::id()){
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized: Cannot report own comment','data'=> '']), 401);
                }
                else{
                    $report_data = [
                        'id_user_reporter' => Auth::id(),
                        'id_user_reported' => $check_comment['user_id'],
                        'id_report_category' => $request->category_id,
                    ];
    
                    $new_report = UserReport::create($report_data);

                    $new_report->comment()->attach($check_comment['id']);

                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Comment reported!','data'=> $new_report]), 200);
                }
            }
            else if($request->content == "profile"){
                $check_user = User::where('id', $request->user_id)->first();

                if(!$check_user){
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Comment not found!','data'=> '']), 404);
                }
                else if($check_user['id'] == Auth::id()){
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized: Cannot report own profile','data'=> '']), 401);
                }
                else{
                    $report_data = [
                        'id_user_reporter' => Auth::id(),
                        'id_user_reported' => $check_user['id'],
                        'id_report_category' => $request->category_id,
                    ];
    
                    $new_report = UserReport::create($report_data);

                    $new_report->profile()->attach($check_user['id']);

                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Profile reported!','data'=> $new_report]), 200);
                }
            }
        }
    }

}