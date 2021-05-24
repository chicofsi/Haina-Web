<?php

namespace App\Http\Controllers\Admin\UserNotification;

use App\Models\NotificationCategory;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\Datatables;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

use Illuminate\Support\Facades\Auth;

use App\Models\AdminLogs;

class ManageNotificationCategory extends Controller
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
            return datatables()->of(NotificationCategory::select('*'))
            ->addColumn('action', function($data){
                    $btn = '<a href="javascript:void(0)" onClick="editFunc('.$data->id.')" data-toggle="tooltip" data-original-title="Edit" class="edit btn btn-primary btn-sm">Edit</a>';

                    $btn = $btn.' <a href="javascript:void(0)" onClick="deleteFunc('.$data->id.')" data-toggle="tooltip" data-original-title="Delete" class="btn btn-danger btn-sm deleteTodo">Delete</a>';

                    return $btn;
                })
            ->addColumn('icon', function($data){
                    return URL::to('storage/'.$data->img);;
                })
            ->addColumn('for', function($data){

                    $btn = ' <span class="label label-success label-mini">'.$data->notification_for.'</span>';
                    return $btn;
                })
            
            ->rawColumns(['action','for'])
            ->make(true);
        }



        return view('admin.notification.category');
        
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
            $notif=NotificationCategory::where('id',$request->id)->first();
            if($request->changePhoto=='1'){
                Storage::disk('public')->delete($notif->img);

                $fileName= str_replace(' ','-', $request->name.'_'.$request->id.'_'.date('d-m-Y_H-i-s'));

                $guessExtension = $request->file('icon')->guessExtension();

                $file = $request->icon->storeAs('public/notification/category/icon',$fileName.'.'.$guessExtension);
                
                $notifCategory   =   NotificationCategory::where('id',$request->id)->update([
                            'name' => $request->name, 
                            'notification_for' => $request->notification_for, 
                            'img' => substr($file,7),
                        ]);

                // AdminLogs::create([
                //    'id_admin' => Auth::id(),
                //    'id_admin_activity' => 9,
                //    'message' => 'Admin edited a job category named '.$request->name
                // ]);
                                 
                return Response()->json($notifCategory);

            }else{
                $notifCategory   =   NotificationCategory::where('id',$request->id)->update([
                    'name' => $request->name, 
                    'notification_for' => $request->notification_for, 
                ]);
                // AdminLogs::create([
                //    'id_admin' => Auth::id(),
                //    'id_admin_activity' => 9,
                //    'message' => 'Admin edited a job category named '.$request->name
                // ]);
                                 
                return Response()->json($notifCategory);

            }
        }else{
            if($request->changePhoto=='1'){
                $fileName= str_replace(' ','-', $request->name.'_'.$request->id.'_'.date('d-m-Y_H-i-s'));

                $guessExtension = $request->file('icon')->guessExtension();

                $file = $request->icon->storeAs('public/notification/category/icon',$fileName.'.'.$guessExtension);
                
                $notifCategory   =   NotificationCategory::create([
                            'name' => $request->name, 
                            'notification_for' => $request->notification_for, 
                            'img' => substr($file,7),
                            ]);    
                // AdminLogs::create([
                //    'id_admin' => Auth::id(),
                //    'id_admin_activity' => 8,
                //    'message' => 'Admin added a job category named '.$request->name
                // ]);
                                 
                return Response()->json($notifCategory);

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
        $notif  = NotificationCategory::where($where)->first();
        $notif['img']= URL::to('storage/'.$notif->img);
      
        return Response()->json($notif);
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
        $notifCategory = NotificationCategory::where('id',$request->id)->first();
        Storage::disk('public')->delete($notifCategory->img);

        // AdminLogs::create([
        //    'id_admin' => Auth::id(),
        //    'id_admin_activity' => 10,
        //    'message' => 'Admin deleted a job category named '.$jobCategory->name
        // ]);

        NotificationCategory::where('id',$request->id)->delete();

        return Response()->json($notifCategory);
    }
}
