<?php

namespace App\Http\Controllers\Api\Ticket;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ValueMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

use App\Http\Resources\FlightSchedule as FlightScheduleResource;

use GuzzleHttp\Client;
use GuzzleHttp\TransferStats;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;
use thiagoalessio\TesseractOCR\TesseractOCR;
use DateTime;

use App\Models\Airports;
use App\Models\DarmawisataSession;
use App\Models\DarmawisataRequest;


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
        $body=[
            'userID'=>$userid,
            'token'=>$token,
            'securityCode'=>$securitycode
        ];
        try {
            $response=$this->client->request(
                'POST',
                'session/login',
                [
                    'form_params' => $body,
                    'on_stats' => function (TransferStats $stats) use (&$url) {
                        $url = $stats->getEffectiveUri();
                    }
                ]  
            );

            $bodyresponse=json_decode($response->getBody()->getContents());
            //return $response;
            DarmawisataRequest::insert(
                [
                    'request'=>json_encode($body),
                    'response'=>json_encode($bodyresponse),
                    'status'=>$bodyresponse->status,
                    'url'=>$url,
                    'response_code'=>$response->getStatusCode()
                ]
            );
            if($bodyresponse->status=="FAILED"){
                if($bodyresponse->respMessage=="member authentication failed"){
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Login Failed!','data'=> '']), 500);
                }
            }else{
                $session=DarmawisataSession::where('id_user',Auth::user()->id)->delete();
                $session=DarmawisataSession::create([
                    'access_token'=>$bodyresponse->accessToken,
                    'id_user'=>Auth::user()->id
                ]);
                return $bodyresponse->accessToken;
            }
        }catch(RequestException $e) {
            return;
        }
    }

    public function checkLoginUser()
    {
        $token=DarmawisataSession::where('id_user',Auth::id())->first()->access_token;
        try {
            $response=$this->client->request(
                'POST',
                'airline/list',
                [
                    'form_params' => [
                        'userID'=>$this->username,
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
                return $this->login();

            }else{
                return $token;
            }
        }catch(RequestException $e) {
            return;
        }
    }
    public function getAirline(Request $request)
    {
        
        $userid=$this->username;
        $token=$this->checkLoginUser();
        $body=[
            'userID'=>$userid,
            'accessToken'=>$token
        ];
        try {
            $response=$this->client->request(
                'POST',
                'airline/list',
                [
                    'form_params' => $body,
                    'on_stats' => function (TransferStats $stats) use (&$url) {
                        $url = $stats->getEffectiveUri();
                    }
                ]  
            );

            $bodyresponse=json_decode($response->getBody()->getContents());
            DarmawisataRequest::insert(
                [
                    'request'=>json_encode($body),
                    'response'=>json_encode($bodyresponse),
                    'status'=>$bodyresponse->status,
                    'url'=>$url,
                    'response_code'=>$response->getStatusCode()
                ]
            );
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
        $token=$this->checkLoginUser();
        $airlineid=$request->airline_id;
        $body=[
            'userID'=>$userid,
            'accessToken'=>$token,
            'airlineID'=>$airlineid
        ];
        try {
            $response=$this->client->request(
                'POST',
                'airline/route',
                [
                    'form_params' => $body,
                    'on_stats' => function (TransferStats $stats) use (&$url) {
                        $url = $stats->getEffectiveUri();
                    }
                ]  
            );

            $bodyresponse=json_decode($response->getBody()->getContents());

            DarmawisataRequest::insert(
                [
                    'request'=>json_encode($body),
                    'response'=>json_encode($bodyresponse),
                    'status'=>$bodyresponse->status,
                    'url'=>$url,
                    'response_code'=>$response->getStatusCode()
                ]
            );

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
        $airports=Airports::where('country',"Indonesia")->orderBy('city')->get();

        return response()->json(new ValueMessage(['value'=>1,'message'=>'Get Airline Routes Success!','data'=> $airports]), 200);
    }

    public function getAirlineSchedule(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'trip_type' => 'required',
            'origin' => 'required',
            'destination' => 'required',
            'depart_date' => 'required',
            'return_date' => 'required',
            'adult' => 'required',
            'child' => 'required',
            'infant' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }else{
            $userid=$this->username;
            $token=$this->checkLoginUser();
            $trip_type=$request->trip_type;
            $origin=$request->origin;
            $destination=$request->destination;
            $depart_date=$request->depart_date;
            $return_date=$request->return_date;
            $adult=$request->adult;
            $child=$request->child;
            $infant=$request->infant;
            if(isset($request->airline_access_code)){
                $airline_access_code=$request->airline_access_code;
            }else{
                $airline_access_code=0;
            }

            try {
                $body=[
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
                    'airlineAccessCode'=>$airline_access_code,
                    'cacheType'=>"Mix",
                    'isShowEachAirline'=>"false"
                ];
                $response=$this->client->request(
                    'POST',
                    'airline/scheduleallairline',
                    [
                        'form_params' => $body,
                        'on_stats' => function (TransferStats $stats) use (&$url) {
                            $url = $stats->getEffectiveUri();
                        }
                    ]  
                );

                $bodyresponse=json_decode($response->getBody()->getContents());


                DarmawisataRequest::insert(
                    [
                        'request'=>json_encode($body),
                        'response'=>json_encode($bodyresponse),
                        'status'=>$bodyresponse->status,
                        'url'=>$url,
                        'response_code'=>$response->getStatusCode()
                    ]
                );
                if($bodyresponse->status=="FAILED"){
                    if($bodyresponse->respMessage=="member authentication failed"){
                        return response()->json(new ValueMessage(['value'=>0,'message'=>'Access Token Wrong!','data'=> '']), 401);
                    }else if($bodyresponse->respMessage=="airline access code is empty or not valid"){
                        return response()->json(new ValueMessage(['value'=>0,'message'=>'Access Code Wrong!','data'=> $bodyresponse->airlineAccessCode]), 401);;
                    }
                }else{
                    $data=[
                        "total_airline"=>$bodyresponse->totalAirline
                    ];

                    foreach ($bodyresponse->journeyDepart as $key => $value) {
                        $data['depart'][$key]=new FlightScheduleResource($value);
                    }
                    if($bodyresponse->journeyReturn){
                        foreach ($bodyresponse->journeyReturn as $key => $value) {
                            $data['return'][$key]=new FlightScheduleResource($value);
                        }
                    }
                    
                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Success!','data'=> $data]), 200);
                }
            }catch(RequestException $e) {
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Access Token Wrong!','data'=> '']), 401);
            }
            return response()->json(new ValueMessage(['value'=>0,'message'=>'not get!','data'=> '']), 401);
        }
    }
    public function getAirlinePrice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'airline' => 'required',
            'trip_type' => 'required',
            'origin' => 'required',
            'destination' => 'required',
            'depart_date' => 'required',
            'return_date' => 'required',
            'adult' => 'required',
            'child' => 'required',
            'infant' => 'required',
            'depart_reference' =>'required',
            'return_reference' =>'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }else{
            $userid=$this->username;
            $token=$this->checkLoginUser();
            $trip_type=$request->trip_type;
            $airline=$request->airline;
            $origin=$request->origin;
            $destination=$request->destination;
            $depart_date=$request->depart_date;
            $return_date=$request->return_date;
            $adult=$request->adult;
            $child=$request->child;
            $infant=$request->infant;
            $depart_reference=$request->depart_reference;
            $return_reference=$request->return_reference;
            if(isset($request->airline_access_code)){
                $airline_access_code=$request->airline_access_code;
            }else{
                $airline_access_code=0;
            }

            try {
                $body=[
                    'userID'=>$userid,
                    'accessToken'=>$token,
                    'airlineID'=>$airline,
                    'tripType'=>$trip_type,
                    'origin'=>$origin,
                    'destination'=>$destination,
                    'departDate'=>$depart_date,
                    'returnDate'=>$return_date,
                    'paxAdult'=>$adult,
                    'paxChild'=>$child,
                    'paxInfant'=>$infant,
                    'airlineAccessCode'=>$airline_access_code,
                    'journeyDepartReference'=>$depart_reference,
                    'journeyReturnReference'=>$return_reference
                ];
                $response=$this->client->request(
                    'POST',
                    'airline/priceallairline',
                    [
                        'form_params' => $body,
                        'on_stats' => function (TransferStats $stats) use (&$url) {
                            $url = $stats->getEffectiveUri();
                        }
                    ]  
                );

                $bodyresponse=json_decode($response->getBody()->getContents());


                DarmawisataRequest::insert(
                    [
                        'request'=>json_encode($body),
                        'response'=>json_encode($bodyresponse),
                        'status'=>$bodyresponse->status,
                        'url'=>$url,
                        'response_code'=>$response->getStatusCode()
                    ]
                );
                if($bodyresponse->status=="FAILED"){
                    if($bodyresponse->respMessage=="member authentication failed"){
                        return response()->json(new ValueMessage(['value'=>0,'message'=>'Access Token Wrong!','data'=> '']), 401);
                    }else if($bodyresponse->respMessage=="airline access code is empty or not valid"){
                        return response()->json(new ValueMessage(['value'=>0,'message'=>'Access Code Wrong!','data'=> $bodyresponse->airlineAccessCode]), 401);;
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


    public function getAirlineAddons(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'airline' => 'required',
            'trip_type' => 'required',
            'origin' => 'required',
            'destination' => 'required',
            'depart_date' => 'required',
            'adult' => 'required',
            'child' => 'required',
            'infant' => 'required',
            'depart_reference' => 'required',
            'contact_title' => 'required',
            'contact_first_name' => 'required',
            'contact_last_name' => 'required',
            'contact_country_code_phone' => 'required',
            'contact_area_code_phone' => 'required',
            'contact_remaining_phone_no' => 'required',
            'pax_details' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }else{
            $userid=$this->username;
            $token=$this->checkLoginUser();
            $trip_type=$request->trip_type;
            $airline=$request->airline;
            $origin=$request->origin;
            $destination=$request->destination;
            $depart_date=$request->depart_date;
            $return_date=$request->return_date;
            $adult=$request->adult;
            $child=$request->child;
            $infant=$request->infant;
            $depart_reference=$request->depart_reference;
            $return_reference=$request->return_reference;
            if(null !== $request->airline_access_code){
                $airline_access_code=$request->airline_access_code;
            }else{
                $airline_access_code=0;
            }

            $pax_details=$request->pax_details;
            foreach ($pax_details as $key => $value) {

                $pax_data[$key]=[
                    "IDNumber" => $value['id_number'] ,
                    "title" => $value['title'] ,
                    "firstName" => $value['first_name'] ,
                    "lastName" => $value['last_name'] ,
                    "birthDate" => $value['birth_date'] ,
                    "gender" => $value['gender'] ,
                    "nationality" => $value['nationality'] ,
                    "birthCountry" => $value['birth_country'] ,
                    "parent" => $value['parent'] ,
                    "type"=> $value['type'] ,
                ];
                if($value['passport_number']){
                    $pax_data[$key]['passportNumber'] = $value['passport_number'] ;
                    $pax_data[$key]['passportIssuedCountry'] = $value['passport_issued_country'] ;
                    $pax_data[$key]['passportIssuedDate'] = $value['passport_issued_date'] ;
                    $pax_data[$key]['passportExpiredDate'] = $value['passport_expired_date'] ;
                }
            }
            try {
                $body=[
                    'userID'=>$userid,
                    'accessToken'=>$token,
                    'airlineID'=>$airline,
                    'tripType'=>$trip_type,
                    'origin'=>$origin,
                    'destination'=>$destination,
                    'departDate'=>$depart_date,
                    'returnDate'=>$return_date,
                    'paxAdult'=>$adult,
                    'paxChild'=>$child,
                    'paxInfant'=>$infant,
                    'airlineAccessCode'=>$airline_access_code,
                    'schDepart'=>$depart_reference,
                    'schReturn'=>$return_reference,
                    'contactTitle' => $request->contact_title,
                    'contactFirstName' => $request->contact_first_name,
                    'contactLastName' => $request->contact_last_name,
                    'contactCountryCodePhone' => $request->contact_country_code_phone,
                    'contactAreaCodePhone' => $request->contact_area_code_phone,
                    'contactRemainingPhoneNo' => $request->contact_remaining_phone_no,
                    'insurance' => $request->insurance,
                    'paxDetails' => $pax_data 
                ];
                $response=$this->client->request(
                    'POST',
                    'airline/baggageandmeal',
                    [
                        'form_params' => $body,
                        'on_stats' => function (TransferStats $stats) use (&$url) {
                            $url = $stats->getEffectiveUri();
                        }
                    ]  
                );

                $bodyresponse=json_decode($response->getBody()->getContents());


                DarmawisataRequest::insert(
                    [
                        'request'=>json_encode($body),
                        'response'=>json_encode($bodyresponse),
                        'status'=>$bodyresponse->status,
                        'url'=>$url,
                        'response_code'=>$response->getStatusCode()
                    ]
                );
                if($bodyresponse->status=="FAILED"){
                    if($bodyresponse->respMessage=="member authentication failed"){
                        return response()->json(new ValueMessage(['value'=>0,'message'=>'Access Token Wrong!','data'=> '']), 401);
                    }else if($bodyresponse->respMessage=="airline access code is empty or not valid"){
                        return response()->json(new ValueMessage(['value'=>0,'message'=>'Access Code Wrong!','data'=> $bodyresponse->airlineAccessCode]), 401);;
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
