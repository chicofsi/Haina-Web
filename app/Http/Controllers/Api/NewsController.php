<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\News;
use App\Models\NewsCategory;
use App\Http\Resources\News as NewsResource;
use App\Http\Resources\ValueMessage;

use GuzzleHttp\Client;
use GuzzleHttp\TransferStats;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use DateTime;

class NewsController extends Controller
{

	//newsapi.ai
	public function __construct()
    {
        $apiKey="9058efb9-8314-4283-8465-6bb0c2a19116";
        $this->client = new Client([
            'base_uri' => 'http://eventregistry.org/api/v1/article/',
            'timeout'  => 100.0,
            'headers' => [
                'apiKey' => $apiKey,
            ]
        ]);
    }

    //////getArticles

    public function getArticle(Request $request)
    {
        $body = [
            "query" => "{\"$query\":{\"$and\":[{\"locationUri\":\"http://en.wikipedia.org/wiki/Indonesia\"},{\"lang\":\"zho\"}]},\"$filter\":{\"forceMaxDataTimeWindow\":\"31\"}}",
            "dataType" => [
                "news"
            ],
            "resultType" => "articles",
            "articlesSortBy" => "date",
            "articlesCount" => 10,
            "includeArticleCategories" => true,
            "includeArticleLocation" => true,
            "includeArticleImage" => true,
            "includeArticleVideos" => true,
            "articleBodyLen" => -1,
            "includeConceptImage" => true,
            "includeConceptDescription" => true,
            "includeSourceDescription" => true,
            "includeSourceLocation" => true
        ];

        $response=$this->client->request(
            'POST',
            'getArticles',
            [
                'form_params' => $body,
                'on_stats' => function (TransferStats $stats) use (&$url) {
                    $url = $stats->getEffectiveUri();
                }
            ]  
        );

        $result = $response->getBody()->getContents();

        if(isset($result)){
            return response()->json(new ValueMessage(['value'=>1,'message'=>'Get News List Success!','data'=> $result]), 200);
        }
        else{
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Error in news!','data'=> '']), 404);
        }
    }

	//news awal

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
