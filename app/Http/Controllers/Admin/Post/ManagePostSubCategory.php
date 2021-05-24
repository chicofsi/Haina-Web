<?php

namespace App\Http\Controllers\Admin\Post;

use App\Models\PostCategory;
use App\Models\PostSubCategory;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\Datatables;

use Illuminate\Support\Facades\Auth;

class ManagePostSubCategory extends Controller
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
    public function index(Request $request)
    {

        if(request()->ajax()) {
            return datatables()->of(PostSubCategory::select('*')->where('id_category',$request->id_category))
            ->addColumn('action', function($data){
                    $btn = '<a href="#subModal" onClick="editsub('.$data->id.')" data-toggle="modal" data-original-title="Edit" class="edit btn btn-primary btn-sm">Edit</a>';

                    $btn = $btn.' <a href="javascript:void(0)" onClick="deletesub('.$data->id.')" data-toggle="tooltip" data-original-title="Delete" class="btn btn-danger btn-sm deleteTodo">Delete</a>';

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
        $postId = $request->id_sub;
 
        $postCategory   =   PostSubCategory::updateOrCreate(
                    [
                     'id' => $postId
                    ],
                    [
                    'id_category' => $request->id_category,
                    'name' => $request->name_sub, 
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
        $post  = PostSubCategory::where($where)->first();
      
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
        $post = PostSubCategory::where('id',$request->id)->delete();
      
        return Response()->json($post);
    }
}
