<?php

namespace App\Http\Controllers\Api\Post\Jobs;

use App\Models\JobCategory;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ValueMessage;
use App\Http\Resources\JobCategory as JobCategoryResource;

class JobsController extends Controller
{
    public function getJobsCategory()
    {
    	$jobCategory=JobCategory::select('id','name','display_name','photo_url')->get();

    	foreach ($jobCategory as $key => $value) {
    		$data[$key]=new JobCategoryResource($value);
    	}

		return response()->json(new ValueMessage(['value'=>1,'message'=>'Get Job Category Success!','data'=> $data]), 200);
    }
}
