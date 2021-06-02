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

use DateTime;


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


            return response()->json(new ValueMessage(['value'=>1,'message'=>'Login Complete!','data'=> $bodyresponse]), 200);
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


            return response()->json(new ValueMessage(['value'=>1,'message'=>'Get Airline List Success!','data'=> $bodyresponse->airlines]), 200);
        }catch(RequestException $e) {
            dd($e);
            return;
        }
    }
}
