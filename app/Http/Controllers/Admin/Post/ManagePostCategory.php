<?php

namespace App\Http\Controllers\Admin\Post;

use App\Models\PostCategory;
use App\Models\PostSubCategory;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\Datatables;

use Illuminate\Support\Facades\Auth;

class ManagePostCategory extends Controller
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
            return datatables()->of(PostCategory::select('*')->with('subcategory'))
            ->addColumn('action', function($data){
                    $btn = '<a href="javascript:void(0)" onClick="editFunc('.$data->id.')" data-toggle="tooltip" data-original-title="Edit" class="edit btn btn-primary btn-sm">Edit</a>';

                    $btn = $btn.' <a href="javascript:void(0)" onClick="deleteFunc('.$data->id.')" data-toggle="tooltip" data-original-title="Delete" class="btn btn-danger btn-sm deleteTodo">Delete</a>';

                    return $btn;
                })
            ->addColumn('sub', function($data){

                    $btn = '';

                    foreach ($data->subcategory as $key => $value) {
                        $btn = $btn.' <span class="label label-success label-mini">'.$value->name.'</span>';
                    }

                    

                    return $btn;
                })
            ->rawColumns(['action','sub'])
            ->make(true);
        }



        return view('admin.post.category');
        
    }

    public function getSubCategory(Request $request)
    {

        if(request()->ajax()) {
            return datatables()->of(PostSubCategory::select('*')->where('id_category',$request->id_category))
            ->addColumn('action', function($data){
                    $btn = '<a href="javascript:void(0)" onClick="editFunc('.$data->id.')" data-toggle="tooltip" data-original-title="Edit" class="edit btn btn-primary btn-sm">Edit</a>';

                    $btn = $btn.' <a href="javascript:void(0)" onClick="deleteFunc('.$data->id.')" data-toggle="tooltip" data-original-title="Delete" class="btn btn-danger btn-sm deleteTodo">Delete</a>';

                    return $btn;
                })
            ->rawColumns(['action'])
            ->make(true);
        }
        
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
        $postId = $request->id;
 
        $postCategory   =   PostCategory::updateOrCreate(
                    [
                     'id' => $postId
                    ],
                    [
                    'name' => $request->name, 
                    ]);    
                         
        return Response()->json($postCategory);
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
