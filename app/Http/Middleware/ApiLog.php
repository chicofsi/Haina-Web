<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Http\Resources\ValueMessage;
use App\Http\Controllers\Api\StaticVariable;
use App\Models\ApiLog;

class ApiLog
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
        $response=$next($request);
        if(Auth::check()){
            $id_user=Auth::id();
        }else{
            $id_user=0;
        }
        ApiLog::create([
            'id_user'=>$id_user,
            'ip_address'=>Request::ip(),
            'url'=>$request->getUri(),
            'method' => $request->getMethod(),
            'request' => $request->all(),
            'response' => $response->getContent()
        ]);
        return $response;
        

    }
}
