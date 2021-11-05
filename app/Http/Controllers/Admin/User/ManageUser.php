<?php

namespace App\Http\Controllers\Admin\User;

use App\Models\User;
use App\Models\UserLogs;
use App\Models\UserActivity;
use App\Models\UserDocs;
use App\Models\City;
use App\Models\PostCategory;
use App\Models\PostSubCategory;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\Datatables;
use Illuminate\Support\Facades\URL;

use Illuminate\Support\Facades\Auth;

class ManageUser extends Controller
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
            return datatables()->of(User::select('*'))
            ->addColumn('action', function($data){
                $btn = '<a href="javascript:void(0)" onClick="detail('.$data->id.')" data-toggle="tooltip" data-original-title="detail" class="btn btn-default btn-sm">Detail</a>';

                return $btn;
                })
            
            ->addColumn('photo', function($data){
                    if($data->photo==null){
                        $data->photo="user_photo/default_user.jpg";
                    }
                    return URL::to('storage/'.$data->photo);
                })
            ->rawColumns(['action'])
            ->make(true);
        }



        return view('admin.user.index');
        
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
        $companyId = $request->id;
 
        $companyupdate   =   Company::where('id',$companyId)->update(
                            [
                                'status' => 'active', 
                            ]);    
                         
        return Response()->json($companyupdate);
    }

    public function suspend(Request $request)
    {
        $companyId = $request->id;
 
        $companyupdate   =   Company::where('id',$companyId)->update(
                            [
                                'status' => 'suspended', 
                            ]);    
                         
        return Response()->json($companyupdate);
    }
    
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        
        $user  = User::where('id',$request->id)->first();

        $userLogs = UserLogs::where('id_user',$request->id)->orderBy('created_at','desc')->get();

        if($user->photo==null){
            $user->photo="user_photo/default_user.jpg";
        }
        
        $user['photo_url']= URL::to('storage/'.$user->photo);

        
        $user['activity']="";
        foreach ($userLogs as $key => $value) {
            if($value->message==null){
                $value->message=UserActivity::where('id',$value->id_user_activity)->first()->default_message;
            }
            $user['activity']=$user['activity']."<li>
                                <div class='avatar'>
                                    <img style='object-fit: contain;' src='".$user->photo_url."' alt=''/>
                                </div>
                                <div class='activity-desk'>
                                    <h5><span>".$value->message."</span></h5>
                                    <p class='text-muted' >".$value->created_at."</p>
                                    
                                </div>
                            </li>";
        }

        $user['resumecount']=UserDocs::where('id_user',$request->id)->where('id_docs_category',1)->count();
        $user['portfoliocount']=UserDocs::where('id_user',$request->id)->where('id_docs_category',2)->count();
        $user['certificatecount']=UserDocs::where('id_user',$request->id)->where('id_docs_category',3)->count();

        // foreach ($company['address'] as $key => $value) {
        //     $company['address'][$key]['city']=City::where('id',$value->id_city)->select('name')->first()->name;
        // }
        // foreach ($company['photo'] as $key => $value) {
        //     $company['photo'][$key]['media_url']= URL::to('storage/'.$value->photo_url);
        // }
      
        return Response()->json($user);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request)
    {
        $where = array('id' => $request->id);
        $post  = PostCategory::where($where)->first();
      
        return Response()->json($post);
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
        $sub = PostSubCategory::where('id_category',$request->id)->delete();
        $post = PostCategory::where('id',$request->id)->delete();
      
        return Response()->json($post);
    }
}
