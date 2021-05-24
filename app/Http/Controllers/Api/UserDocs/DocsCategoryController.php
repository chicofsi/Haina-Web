<?php

namespace App\Http\Controllers\Api\UserDocs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ValueMessage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Models\DocsCategory;
use App\Http\Resources\DocsCategory as DocsCategoryResource;

class DocsCategoryController extends Controller
{
    
    public function getCategory(Request $request)
    {
        $docs=DocsCategory::select('id','name','icon_url')->get();

        foreach ($docs as $key => $value) {
            $data[$key]=new DocsCategoryResource($value);
        }

        return response()->json(new ValueMessage(['value'=>1,'message'=>'Get Document Category Success!','data'=> $data]), 200);
    
    }    
}
