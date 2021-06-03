<?php

namespace App\Http\Controllers\Api\News;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ValueMessage;
use Illuminate\Support\Facades\Validator;

use GuzzleHttp\Client;
use GuzzleHttp\TransferStats;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use DateTime;


class NewsController extends Controller
{

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

    //getArticles

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
            return response()->json(new ValueMessage(['value'=>1,'message'=>'Get News List Success!','data'=> $list_pending]), 200);
        }
        else{
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Error in getting transaction!','data'=> '']), 404);
        }
    }

}