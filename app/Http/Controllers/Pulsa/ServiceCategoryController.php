<?php

namespace App\Http\Controllers\Pulsa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ValueMessage;
use App\Http\Resources\CategoryServiceResource;

use App\Models\Transaction;
use App\Models\Providers;
use App\Models\ProvidersPrefix;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductGroup;
use App\Models\PaymentMethod;
use App\Models\PaymentMethodCategory;
use App\Models\CategoryService;

use DateTime;

class ServiceCategoryController extends Controller
{
    public function getServiceCategory(Request $request)
    {
        $post = CategoryService::with('productCategory');

        $post = $post->get();

        if($post->isEmpty()){
            return response()->json(new ValueMessage(['value'=>0, 'message'=> 'Get Category Service Failed', 'data'=>'']), 404);
        } else {
            foreach($post as $key => $value){
                $postData[$key] = new CategoryServiceResource($value);
            }
        return response()->json(new ValueMessage(['value'=>1, 'message'=> 'Get Category Service Success', 'data'=> $postData]), 200);
        }        
    }
}
