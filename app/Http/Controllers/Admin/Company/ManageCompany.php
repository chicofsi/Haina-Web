<?php

namespace App\Http\Controllers\Admin\Company;

use App\Models\Company;
use App\Models\City;
use App\Models\PostCategory;
use App\Models\PostSubCategory;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\Datatables;
use Illuminate\Support\Facades\URL;

use Illuminate\Support\Facades\Auth;

use App\Models\AdminLogs;
use App\Models\UserLogs;

class ManageCompany extends Controller
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
            return datatables()->of(Company::with('address', 'photo', 'user'))
            ->addColumn('action', function($data){
                $btn = '<a href="javascript:void(0)" onClick="detail('.$data->id.')" data-toggle="tooltip" data-original-title="detail" class="btn btn-default btn-sm">Detail</a>';

                return $btn;
                })
            ->addColumn('stat', function($data){
                    if($data->status=='pending review'){
                        $btn = ' <span class="label label-warning label-mini">'.$data->status.'</span>';
                    }else if($data->status=='active'){
                        $btn = ' <span class="label label-success label-mini">'.$data->status.'</span>';
                    }else if($data->status=='suspended'){
                        $btn = ' <span class="label label-danger label-mini">'.$data->status.'</span>';
                    }
                    return $btn;
                })
            ->addColumn('photo', function($data){
                    return URL::to('storage/'.$data->icon_url);
                })
            ->rawColumns(['action', 'stat'])
            ->make(true);
        }



        return view('admin.company.index');
        
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
        $company=Company::where('id',$companyId)->first();

        AdminLogs::create([
           'id_admin' => Auth::id(),
           'id_admin_activity' => 3,
           'message' => 'Admin approved a company named '.$company->name
        ]);

        UserLogs::create([
           'id_user' => $company->id_user,
           'id_user_activity' => 10,
           'message' => 'Admin approved the company named '.$company->name
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
        $company=Company::where('id',$companyId)->first();

        AdminLogs::create([
           'id_admin' => Auth::id(),
           'id_admin_activity' => 4,
           'message' => 'Admin suspended a company named '.$company->name
        ]);

        UserLogs::create([
           'id_user' => $company->id_user,
           'id_user_activity' => 11,
           'message' => 'Admin suspended the company named '.$company->name
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
        
        $company  = Company::where('id',$request->id)->with('address','photo')->first();
        $company['photo_url']= URL::to('storage/'.$company->icon_url);
        foreach ($company['address'] as $key => $value) {
            $company['address'][$key]['city']=City::where('id',$value->id_city)->select('name')->first()->name;
        }
        foreach ($company['photo'] as $key => $value) {
            $company['photo'][$key]['photo_url']= URL::to('storage/'.$value->photo_url);
        }
      
        return Response()->json($company);
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
