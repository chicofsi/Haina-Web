<?php

namespace App\Http\Controllers\Admin\Post;

use App\Models\Post;
use App\Models\PostCategory;
use App\Models\PostSubCategory;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\Datatables;
use Illuminate\Support\Facades\URL;

use Illuminate\Support\Facades\Auth;

class ManagePost extends Controller
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
            return datatables()->of(Post::with('creator','subcategory')->where('id_subcategory','1'))
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
            ->addColumn('sub', function($data){

                    $btn = ' <span class="label label-success label-mini">'.$data->subcategory->name.'</span>';
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
            ->rawColumns(['action','sub','stat'])
            ->make(true);
        }



        return view('admin.post.index');
        
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
 
        $postupdate   =   Post::where('id',$postId)->update(
                            [
                                'status' => 'accepted', 
                            ]);    
                         
        return Response()->json($postupdate);
    }

    public function block(Request $request)
    {
        $postId = $request->id;
 
        $postupdate   =   Post::where('id',$postId)->update(
                            [
                                'status' => 'blocked', 
                            ]);    
                         
        return Response()->json($postupdate);
    }
    
    public function close(Request $request)
    {
        $postId = $request->id;
 
        $postupdate   =   Post::where('id',$postId)->update(
                            [
                                'status' => 'closed', 
                            ]);    
                         
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
