<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PostCategory;
use App\Http\Resources\ValueMessage;

use GuzzleHttp\Client;
use GuzzleHttp\TransferStats;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use DateTime;

class PostCategoryController extends Controller
{
    public function getCategory(){

        $category = PostCategory::all();

        if(!$category){
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Post Category Not Found!','data'=> '']), 404);
        }
        else{
            return response()->json(new ValueMessage(['value'=>1,'message'=>'Get Post Category Successful!','data'=> $category]), 200);
        }
    }
}
