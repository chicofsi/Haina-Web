<?php

namespace App\Http\Controllers\Api\Post;

use App\Models\PostCategory;
use App\Models\PostSubCategory;
use App\Models\Post;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ValueMessage;
use App\Http\Resources\Post as PostResource;

class PostController extends Controller
{
    public function getPostCategory()
    {
    	$postCategory=PostCategory::select('id','name')->get();

		return response()->json(new ValueMessage(['value'=>1,'message'=>'Get Post Category Success!','data'=> $postCategory]), 200);
    }
    public function getPostSubCategory(Request $request)
    {
        if($request->has('id_category')){
            $postSubCategory=PostSubCategory::where('id_category',$request->id_category)->select('id','name')->get();
            $data=$postSubCategory;
        
        }else{
            $postSubCategory=PostSubCategory::select('id','name','id_category')->with('category')->get();
            foreach ($postSubCategory as $key => $value) {
                $data[$key]=[
                    "id"=>$value->id,
                    "name"=>$value->name,
                    "category"=>$value->category->name
                ];
            }
        }

        if($postSubCategory->isEmpty()){
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Post Sub Category Doesn\'t Exist!','data'=> '']), 404);
        }else{
            return response()->json(new ValueMessage(['value'=>1,'message'=>'Get Post SubCategory Success!','data'=> $data]), 200);
        }
    }

    public function getPost(Request $request)
    {
        if($request->has('id_post')){
            $post= Post::with('creator','subcategory')->where('id',$request->id_post)->where('status','accepted')->get();
        }
        else if($request->has('id_subcategory')){
            $post= Post::with('creator','subcategory')->where('id_subcategory',$request->id_subcategory)->where('status','accepted')->get();
        }
        else{
            $post=Post::with('creator','subcategory')->where('status','accepted')->get();
        }

        if($post->isEmpty()){
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Post Doesn\'t Exist!','data'=> '']), 404);
        }else{
            foreach ($post as $key => $value) {
                $postData[$key] =new PostResource($value);
            }
            return response()->json(new ValueMessage(['value'=>1,'message'=>'Get Post List Success!','data'=> $postData]), 200);
        }

    }
    public function getMyPost(Request $request)
    {
        
        $post=Post::with('creator','subcategory')->where('id_user',$request->user()->id)->get();
        
        if($post->isEmpty()){
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Post Doesn\'t Exist!','data'=> '']), 404);
        }else{
            foreach ($post as $key => $value) {
                $postData[$key] =new PostResource($value);
            }
            return response()->json(new ValueMessage(['value'=>1,'message'=>'Get Post List Success!','data'=> $postData]), 200);
        }

    
    }
    
}
