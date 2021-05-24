<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\News;
use App\Models\NewsCategory;
use App\Http\Resources\News as NewsResource;
use App\Http\Resources\ValueMessage;

class NewsController extends Controller
{

    public $successStatus = 200;

    public function index(){
    	$url = "https://www.news.developeridn.com/";

   		$json = json_decode(file_get_contents($url), true);
        

   		return response()->json(new ValueMessage(['value'=>1,'message'=>'Get News Success','data'=> $json['data']]),$this->successStatus);
    }

    public function getNews(Request $request){
    	if($request->has('id_news')){
    		$news= News::select('id','title','url','photo_url','id_category')->with('category')->where('id',$request->id_news)->get();
    	}
    	else if($request->has('id_category')){
    		$news= News::select('id','title','url','photo_url','id_category')->with('category')->where('id_category',$request->id_category)->get();
    	}else{
    		$news= News::select('id','title','url','photo_url','id_category')->with('category')->get();
    	}

    	$newsData=null;

    	foreach ($news as $key => $value) {
    		$newsData[$key]=new NewsResource($value);
    	}
    	if(!$newsData){
    		return response()->json(new ValueMessage(['value'=>0,'message'=>'News Doesn\'t Exist!','data'=> '']), 404);
    	}

    	return response()->json(new ValueMessage(['value'=>1,'message'=>'Get News Success!','data'=> $newsData]), $this->successStatus);

    }

    public function getNewsCategory(){
    	$newsCategory= NewsCategory::select('id','name')->get();

    	return response()->json(new ValueMessage(['value'=>1,'message'=>'Get News Category Success!','data'=> $newsCategory]), $this->successStatus);
    }
}
