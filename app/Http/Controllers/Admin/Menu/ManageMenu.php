<?php

namespace App\Http\Controllers\Admin\Menu;

use App\Models\AdminMenu;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\Datatables;

class ManageMenu extends Controller
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
            return datatables()->of(AdminMenu::select('*'))
            ->addColumn('action', function($data){
                    $btn = '<a href="javascript:void(0)" onClick="editFunc('.$data->id.')" data-toggle="tooltip" data-original-title="Edit" class="edit btn btn-primary btn-sm">Edit</a>';

                    $btn = $btn.' <a href="javascript:void(0)" onClick="deleteFunc('.$data->id.')" data-toggle="tooltip" data-original-title="Delete" class="btn btn-danger btn-sm deleteTodo">Delete</a>';

                    return $btn;
                })
            ->rawColumns(['action'])
            ->make(true);
        }
        return view('admin.menu.index');
        
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
    public function store(Request $request)
    {
        $menuId = $request->id;
        if(!empty($request->active)) {
            $active=1;
        }else{
            $active=0;
        }

        if(!empty($request->admin_access)) {
            $admin_access=1;
        }else{
            $admin_access=0;
        }
 
        $menu   =   AdminMenu::updateOrCreate(
                    [
                     'id' => $menuId
                    ],
                    [
                    'menu_name' => $request->menu_name, 
                    'route' => $request->route,
                    'active' => $active,
                    'admin_access' => $admin_access
                    ]);    
                         
        return Response()->json($menu);
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
        $menu  = AdminMenu::where($where)->first();
      
        return Response()->json($menu);
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
        $menu = AdminMenu::where('id',$request->id)->delete();
      
        return Response()->json($menu);
    }
}
