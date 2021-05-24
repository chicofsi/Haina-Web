<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Http\Resources\ValueMessage;
use App\Http\Controllers\Api\StaticVariable;

class KeyCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if($request->headers->has('apikey')){
            if($request->header('apikey') == StaticVariable::$apiKey){
                return $next($request);
            }
            else{
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Api Key Wrong!','data'=> '']), 503);
            } 
        }else{
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized!','data'=> '']), 503);
        }
        

    }
}
