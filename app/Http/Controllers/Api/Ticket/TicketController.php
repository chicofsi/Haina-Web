<?php

namespace App\Http\Controllers\Api\Ticket;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ValueMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

use GuzzleHttp\Client;
use GuzzleHttp\TransferStats;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use thiagoalessio\TesseractOCR\TesseractOCR;
use DateTime;

use App\Models\Airports;


class TicketController extends Controller
{
    public function __construct()
    {
        $this->username="HAYQ18MKPK";
        $this->password="HAQQQ8MKPK";
        $this->client = new Client([
            'verify' => false,
            'base_uri' => 'https://61.8.74.42:7080/h2h/',
            'timeout'  => 150.0
        ]);
    }
    public function login()
    {
        $userid=$this->username;
        $token=date('Y-m-d').'T'.date('H:i:s');
        $securitycode=md5($token.md5($this->password));

        try {
            $response=$this->client->request(
                'POST',
                'session/login',
                [
                    'form_params' => [
                        'userID'=>$userid,
                        'token'=>$token,
                        'securityCode'=>$securitycode
                    ],
                    'on_stats' => function (TransferStats $stats) use (&$url) {
                        $url = $stats->getEffectiveUri();
                    }
                ]  
            );

            $bodyresponse=json_decode($response->getBody()->getContents());
            //return $response;

            if($bodyresponse->status=="FAILED"){
                if($bodyresponse->respMessage=="member authentication failed"){
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Login Failed!','data'=> '']), 500);
                }
            }else{

                return response()->json(new ValueMessage(['value'=>1,'message'=>'Login Complete!','data'=> $bodyresponse]), 200);
            }


        }catch(RequestException $e) {
            dd($e);
            return;
        }
    }
    public function getAirline(Request $request)
    {
        $userid=$this->username;
        $token=$request->token;

        try {
            $response=$this->client->request(
                'POST',
                'airline/list',
                [
                    'form_params' => [
                        'userID'=>$userid,
                        'accessToken'=>$token
                    ],
                    'on_stats' => function (TransferStats $stats) use (&$url) {
                        $url = $stats->getEffectiveUri();
                    }
                ]  
            );

            $bodyresponse=json_decode($response->getBody()->getContents());
            //return $response;
            if($bodyresponse->status=="FAILED"){
                if($bodyresponse->respMessage=="member authentication failed"){
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Access Token Wrong!','data'=> '']), 401);
                }
            }else{

                return response()->json(new ValueMessage(['value'=>1,'message'=>'Get Airline List Success!','data'=> $bodyresponse->airlines]), 200);
            }
        }catch(RequestException $e) {
            dd($e);
            return;
        }
    }

    public function getRoute(Request $request)
    {
        $userid=$this->username;
        $token=$request->token;
        $airlineid=$request->airline_id;

        try {
            $response=$this->client->request(
                'POST',
                'airline/route',
                [
                    'form_params' => [
                        'userID'=>$userid,
                        'airlineID'=>$airlineid,
                        'accessToken'=>$token
                    ],
                    'on_stats' => function (TransferStats $stats) use (&$url) {
                        $url = $stats->getEffectiveUri();
                    }
                ]  
            );

            $bodyresponse=json_decode($response->getBody()->getContents());
            //return $response;
            if($bodyresponse->status=="FAILED"){
                if($bodyresponse->respMessage=="member authentication failed"){
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Access Token Wrong!','data'=> '']), 401);
                }
            }else{

                return response()->json(new ValueMessage(['value'=>1,'message'=>'Get Airline Routes Success!','data'=> $bodyresponse->routes]), 200);
            }
        }catch(RequestException $e) {
            dd($e);
        }
    }

    public function getAirport(Request $request)
    {
        $airports=Airports::where('country',"Indonesia")->get();

        return response()->json(new ValueMessage(['value'=>1,'message'=>'Get Airline Routes Success!','data'=> $airports]), 200);
    }

    public function getAirlineSchedule(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'trip_type' => 'required',
            'origin' => 'required',
            'destination' => 'required',
            'depart_date' => 'required',
            'return_date' => 'required',
            'adult' => 'required',
            'child' => 'required',
            'infant' => 'required',
            'airline_access_code' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }else{
            $userid=$this->username;
            $token=$request->token;
            $trip_type=$request->trip_type;
            $origin=$request->origin;
            $destination=$request->destination;
            $depart_date=$request->depart_date;
            $return_date=$request->return_date;
            $adult=$request->adult;
            $child=$request->child;
            $infant=$request->infant;

            try {
                $response=$this->client->request(
                    'POST',
                    'airline/scheduleallairline',
                    [
                        'form_params' => [
                            'userID'=>$userid,
                            'accessToken'=>$token,
                            'tripType'=>$trip_type,
                            'origin'=>$origin,
                            'destination'=>$destination,
                            'departDate'=>$depart_date,
                            'returnDate'=>$return_date,
                            'paxAdult'=>$adult,
                            'paxChild'=>$child,
                            'paxInfant'=>$infant,
                            'airlineAccessCode'=>$request->airline_access_code,
                            'cacheType'=>"Mix",
                            'isShowEachAirline'=>"false"
                        ],
                        'on_stats' => function (TransferStats $stats) use (&$url) {
                            $url = $stats->getEffectiveUri();
                        }
                    ]  
                );

                $bodyresponse=json_decode($response->getBody()->getContents());
                //return $response;
                if($bodyresponse->status=="FAILED"){
                    if($bodyresponse->respMessage=="member authentication failed"){
                        return response()->json(new ValueMessage(['value'=>0,'message'=>'Access Token Wrong!','data'=> '']), 401);
                    }
                }else{

                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Success!','data'=> $bodyresponse]), 200);
                }
            }catch(RequestException $e) {
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Access Token Wrong!','data'=> '']), 401);
            }
            return response()->json(new ValueMessage(['value'=>0,'message'=>'not get!','data'=> '']), 401);
        }
    }
}
