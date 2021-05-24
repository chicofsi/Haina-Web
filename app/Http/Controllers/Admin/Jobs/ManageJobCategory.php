<?php

namespace App\Http\Controllers\Admin\Jobs;

use App\Models\JobVacancy;
use App\Models\JobCategory;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\Datatables;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

use Illuminate\Support\Facades\Auth;

use App\Models\AdminLogs;

class ManageJobCategory extends Controller
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
            return datatables()->of(JobCategory::select('*'))
            ->addColumn('action', function($data){
                    $btn = '<a href="javascript:void(0)" onClick="editFunc('.$data->id.')" data-toggle="tooltip" data-original-title="Edit" class="edit btn btn-primary btn-sm">Edit</a>';

                    $btn = $btn.' <a href="javascript:void(0)" onClick="deleteFunc('.$data->id.')" data-toggle="tooltip" data-original-title="Delete" class="btn btn-danger btn-sm deleteTodo">Delete</a>';

                    return $btn;
                })
            ->addColumn('icon', function($data){
                    return URL::to('storage/'.$data->photo_url);;
                })
            
            ->rawColumns(['action'])
            ->make(true);
        }



        return view('admin.jobs.category');
        
    }



    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        

        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if($request->type=="edit"){
            $job=JobCategory::where('id',$request->id)->first();
            if($request->changePhoto=='1'){
                Storage::disk('public')->delete($job->photo_url);

                $fileName= str_replace(' ','-', $request->name.'_'.$request->id.'_'.date('d-m-Y_H-i-s'));

                $guessExtension = $request->file('icon')->guessExtension();

                $file = $request->icon->storeAs('public/jobs/category/icon',$fileName.'.'.$guessExtension);
                
                $jobCategory   =   JobCategory::where('id',$request->id)->update([
                            'name' => $request->name, 
                            'display_name' => $request->display_name, 
                            'photo_url' => substr($file,7),
                        ]);

                AdminLogs::create([
                   'id_admin' => Auth::id(),
                   'id_admin_activity' => 9,
                   'message' => 'Admin edited a job category named '.$request->name
                ]);
                                 
                return Response()->json($jobCategory);

            }else{
                $jobCategory   =   JobCategory::where('id',$request->id)->update([
                    'name' => $request->name, 
                    'display_name' => $request->display_name, 
                ]);
                AdminLogs::create([
                   'id_admin' => Auth::id(),
                   'id_admin_activity' => 9,
                   'message' => 'Admin edited a job category named '.$request->name
                ]);
                                 
                return Response()->json($jobCategory);

            }
        }else{
            if($request->changePhoto=='1'){
                $fileName= str_replace(' ','-', $request->name.'_'.$request->id.'_'.date('d-m-Y_H-i-s'));

                $guessExtension = $request->file('icon')->guessExtension();

                $file = $request->icon->storeAs('public/jobs/category/icon',$fileName.'.'.$guessExtension);
                
                $jobCategory   =   JobCategory::create([
                            'name' => $request->name, 
                            'display_name' => $request->display_name, 
                            'photo_url' => substr($file,7),
                            ]);    
                AdminLogs::create([
                   'id_admin' => Auth::id(),
                   'id_admin_activity' => 8,
                   'message' => 'Admin added a job category named '.$request->name
                ]);
                                 
                return Response()->json($jobCategory);

            }else{

            }
        }
        
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
        $where = array('id' => $request->id);
        $post  = JobCategory::where($where)->first();
        $post['photo_url']= URL::to('storage/'.$post->photo_url);
      
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
        $jobCategory = JobCategory::where('id',$request->id)->first();
        Storage::disk('public')->delete($jobCategory->photo_url);

        AdminLogs::create([
           'id_admin' => Auth::id(),
           'id_admin_activity' => 10,
           'message' => 'Admin deleted a job category named '.$jobCategory->name
        ]);

        JobCategory::where('id',$request->id)->delete();

        return Response()->json($jobCategory);
    }
}
