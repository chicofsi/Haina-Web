<?php

namespace App\Http\Controllers\Admin\Jobs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\Datatables;
use Illuminate\Support\Facades\URL;

use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Api\Notification\NotificationController;

use App\Models\AdminLogs;
use App\Models\NotificationCategory;
use App\Models\PersonalAccessToken;
use App\Models\JobVacancy;
use App\Models\City;
use App\Models\User;
use App\Models\Company;


class ManageJobs extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        if(request()->ajax()) {
            return datatables()->of(JobVacancy::with('address','category','company'))
            ->addColumn('action', function($data){
                if($data->status=='pending'){
                    $btn = '<a href="javascript:void(0)" onClick="acc('.$data->id.')" data-toggle="tooltip" data-original-title="Edit" class="btn btn-success btn-sm">Accept</a>';

                    $btn = $btn.' <a href="javascript:void(0)" onClick="block('.$data->id.')" data-toggle="tooltip" data-original-title="Delete" class="btn btn-danger btn-sm deleteTodo">Block</a>';
                }else if($data->status=='accepted'){
                    $btn = '<a href="javascript:void(0)" onClick="clos('.$data->id.')" data-toggle="tooltip" data-original-title="Edit" class="btn btn-primary btn-sm">Close</a>';
                }else if($data->status=='blocked'){
                    $btn = '';
                }else if($data->status=='closed'){
                    $btn = '';
                }
                    

                    

                    return $btn;
                })
            ->addColumn('category', function($data){

                    $btn = ' <span class="label label-success label-mini">'.$data->category->display_name.'</span>';
                    return $btn;
                })
            ->addColumn('stat', function($data){
                    if($data->status=='pending'){
                        $btn = ' <span class="label label-warning label-mini">'.$data->status.'</span>';
                    }else if($data->status=='accepted'){
                        $btn = ' <span class="label label-success label-mini">'.$data->status.'</span>';
                    }else if($data->status=='blocked'){
                        $btn = ' <span class="label label-danger label-mini">'.$data->status.'</span>';
                    }else if($data->status=='closed'){
                        $btn = ' <span class="label label-default label-mini">'.$data->status.'</span>';
                    }
                    return $btn;
                })
            ->addColumn('date', function($data){
                    return date_format($data->created_at,'d F Y');
                })
            ->addColumn('photo', function($data){
                    return URL::to('storage/'.$data->photo_url);
                })
            ->addColumn('city', function($data){
                    $city=City::where('id',$data->address->id_city)->first();
                    return $city->name;
                })
            ->rawColumns(['action','category','stat'])
            ->make(true);
        }



        return view('admin.jobs.index');
        
    }



    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function accept(Request $request)
    {
        $postId = $request->id;
 
        $postupdate   =   JobVacancy::where('id',$postId)->update(
                            [
                                'status' => 'accepted', 
                            ]);    

        $post = JobVacancy::where('id',$postId)->first()->title;
        AdminLogs::create([
           'id_admin' => Auth::id(),
           'id_admin_activity' => 5,
           'message' => 'Admin approved a job vacancy titled '.$post
        ]);

        $company = JobVacancy::select('id_company', 'name')->where('id', $postId)->first();
        $user_id = Company::select('id_user')->where('id', $company['id_company'])->first();

        $token = [];
        $usertoken = PersonalAccessToken::select('name')->where('tokenable_id', $user_id['id_user'])->get();

        foreach($usertoken as $key => $value){
            array_push($token, $value->name); 
        }

        foreach ($token as $key => $value) {
            NotificationController::sendPush($value, "Job Posting Approved", $post." in ".$company['name']. " is approved", "Job", "");
        }
                         
        return Response()->json($postupdate);
    }

    public function block(Request $request)
    {
        $postId = $request->id;
 
        $postupdate   =   JobVacancy::where('id',$postId)->update(
                            [
                                'status' => 'blocked', 
                            ]);    
        $post = JobVacancy::where('id',$postId)->first()->title;
        AdminLogs::create([
           'id_admin' => Auth::id(),
           'id_admin_activity' => 6,
           'message' => 'Admin blocked a job vacancy titled '.$post
        ]);

        $company = JobVacancy::select('id_company', 'name')->where('id', $postId)->first();
        $user_id = Company::select('id_user')->where('id', $company['id_company'])->first();

        $token = [];
        $usertoken = PersonalAccessToken::select('name')->where('tokenable_id', $user_id['id_user'])->get();

        foreach($usertoken as $key => $value){
            array_push($token, $value->name); 
        }

        foreach ($token as $key => $value) {
            NotificationController::sendPush($value, "Job Posting Rejected", $post." in ".$company['name']. " is rejected. Please contact admin for more details.", "Job", "");
        }
                         
        return Response()->json($postupdate);
    }
    
    public function close(Request $request)
    {
        $postId = $request->id;
 
        $postupdate   =   JobVacancy::where('id',$postId)->update(
                            [
                                'status' => 'closed', 
                            ]);    
        $post = JobVacancy::where('id',$postId)->first()->title;
        AdminLogs::create([
           'id_admin' => Auth::id(),
           'id_admin_activity' => 7,
           'message' => 'Admin closed a job vacancy titled '.$post
        ]);

        $company = JobVacancy::select('id_company', 'name')->where('id', $postId)->first();
        $user_id = Company::select('id_user')->where('id', $company['id_company'])->first();

        $token = [];
        $usertoken = PersonalAccessToken::select('name')->where('tokenable_id', $user_id['id_user'])->get();

        foreach($usertoken as $key => $value){
            array_push($token, $value->name); 
        }

        foreach ($token as $key => $value) {
            NotificationController::sendPush($value, "Job Posting Closed", $post." in ".$company['name']. " has been closed by admin.", "Job", "");
        }
                         
        return Response()->json($postupdate);
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request)
    {
       
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */ 
    public function destroy(Request $request)
    {
       
    }
}
