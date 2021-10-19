<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Http\Resources\ValueMessage;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class EmailVerified
{
    public function handle(Request $request, Closure $next)
    {
        $response=$next($request);

        if(Auth::check()){
            $id_user=Auth::id();

            $check = User::where('id', $id_user)->first();
            if($check['email_verified_at'] == null){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Email not verified!','data'=> '']), 503);
            }
            else{
                return $response;
            }
        }else{
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized!','data'=> '']), 503);
        }
    }

}