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
use App\Models\FlightBookingSession;
use App\Models\FlightTripSession;
use App\Models\FlightPassengerSession;
use App\Models\FlightAddonsSession;


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
        $token=DarmawisataSession::where('id_user',Auth::id())->whereRaw(' created_at > DATE_SUB( NOW(), INTERVAL 15 MINUTE )')->first();
        if($token){
            return $token->access_token;
        }else{
            DarmawisataSession::where('id_user',Auth::id())->delete();
            return $this->login();
        }
    }
    public function deleteSession($id_user)
    {
        $flightbooking=FlightBookingSession::where('id_user',$id_user)->get();
        foreach ($flightbooking as $key => $value) {
            $passenger=FlightPassengerSession::where('id_flight_booking_session',$value->id)->get();

            foreach ($passenger as $k => $v) {
                $addons=FlightAddonsSession::where('id_flight_passenger_session',$v->id)->delete();
            }
            $flighttrip=FlightTripSession::where('id_flight_booking_session', $value->id)->delete();
            $passenger=FlightPassengerSession::where('id_flight_booking_session',$value->id)->delete();

        }        
        $flightbooking=FlightBookingSession::where('id_user',$id_user)->delete();
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

    public function getNationality(Request $request)
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
                'airline/nationality',
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

                return response()->json(new ValueMessage(['value'=>1,'message'=>'Get Nationality Success!','data'=> $bodyresponse]), 200);
            }
        }catch(RequestException $e) {
            dd($e);
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

    //step 1
    public function getAirlineSchedule(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'trip_type' => 'required',
            'origin' => 'required',
            'destination' => 'required',
            'depart_date' => 'required',
            'adult' => 'required',
            'child' => 'required',
            'infant' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }else{
            $this->deleteSession(Auth::id());

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

            if($adult==3){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Access Code Wrong!','data'=> "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"]), 403);
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
                        return response()->json(new ValueMessage(['value'=>0,'message'=>'Access Code Wrong!','data'=> $bodyresponse->airlineAccessCode]), 403);
                    }
                }else{
                    $body=[
                        'id_user' => Auth::id(),
                        'trip_type' => $trip_type,
                        'origin' => $origin,
                        'destination' => $destination,
                        'depart_date'=>$depart_date,
                        'pax_adult'=>$adult,
                        'pax_child'=>$child,
                        'pax_infant'=>$infant
                    ];
                    if($return_date){
                        $body['return_date']=$return_date;
                    }
                    FlightBookingSession::create($body);

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

    //step 2
    public function checkSession($id_user)
    {
        $bookingsession=FlightBookingSession::where('id_user',$id_user)->first();
        if($bookingsession){
            return $bookingsession;
        }else{
            return false;
        }
    }

    //step 3
    public function getAirlinePrice(Request $request)
    {
        $bookingsession=$this->checkSession(Auth::id());
        if(! $bookingsession){
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Search Flight Schedule First!','data'=> '']), 401);
        }else{
            $validator = Validator::make($request->all(), [
                'airline' => 'required',
                'depart' =>'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['error'=>$validator->errors()], 400);
            }else{
                $userid=$this->username;
                $token=$this->checkLoginUser();
                $trip_type=$bookingsession->trip_type;
                $airline=$request->airline;
                $origin=$bookingsession->origin;
                $destination=$bookingsession->destination;
                $depart_date=$bookingsession->depart_date;
                $return_date=$bookingsession->return_date;
                $adult=$bookingsession->pax_adult;
                $child=$bookingsession->pax_child;
                $infant=$bookingsession->pax_infant;
                $depart_reference=$request->depart['journey_references'];
                if(isset($request->return['journey_references'])){
                    $return_reference=$request->return['journey_references'];
                }else{
                    $return_reference="";

                }

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
                        'airlineAccessCode'=>$airline_access_code,
                        'paxChild'=>$child,
                        'paxInfant'=>$infant,
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
                        FlightTripSession::where('id_flight_booking_session',$bookingsession->id)->delete();
                        foreach ($request->depart['flight_detail'] as $key => $value) {
                            $tripdepart=FlightTripSession::create([
                                "id_flight_booking_session" => $bookingsession->id,
                                "type" => "depart",
                                "airline_code" => $value['flightDetail'][0]['airlineCode'],
                                "flight_number" => $value['flightDetail'][0]['flightNumber'],
                                "sch_origin" => $value['flightDetail'][0]['fdOrigin'],
                                "sch_destination" => $value['flightDetail'][0]['fdDestination'],
                                "sch_depart_time" => $value['flightDetail'][0]['fdDepartTime'],
                                "sch_arrival_time" => $value['flightDetail'][0]['fdArrivalTime'],
                                "flight_class" => $value['availableDetail'][0]['flightClass'],
                                "detail_schedule" => $bodyresponse->priceDepart[$key]->classFare,
                                "garuda_number" => $bodyresponse->priceDepart[$key]->garudaNumber,
                                "garuda_availability" => $bodyresponse->priceDepart[$key]->garudaAvailability,
                            ]);
                        }
                        if(isset($request->return['flight_detail'])){
                            foreach ($request->return['flight_detail'] as $key => $value) {
                                $tripdepart=FlightTripSession::create([
                                    "id_flight_booking_session" => $bookingsession->id,
                                    "type" => "return",
                                    "airline_code" => $value['flightDetail'][0]['airlineCode'],
                                    "flight_number" => $value['flightDetail'][0]['flightNumber'],
                                    "sch_origin" => $value['flightDetail'][0]['fdOrigin'],
                                    "sch_destination" => $value['flightDetail'][0]['fdDestination'],
                                    "sch_depart_time" => $value['flightDetail'][0]['fdDepartTime'],
                                    "sch_arrival_time" => $value['flightDetail'][0]['fdArrivalTime'],
                                    "flight_class" => $value['availableDetail'][0]['flightClass'],
                                    "detail_schedule" => $bodyresponse->priceReturn[$key]->classFare,
                                    "garuda_number" => $bodyresponse->priceReturn[$key]->garudaNumber,
                                    "garuda_availability" => $bodyresponse->priceReturn[$key]->garudaAvailability
                                ]);
                            }
                        }
                        
                        
                        $bookingsession=FlightBookingSession::where('id_user',Auth::id())->update([
                            'airline_id'=>$airline,
                            'depart_reference'=>$depart_reference,
                            'return_reference'=>$return_reference
                        ]);
                        
                        return response()->json(new ValueMessage(['value'=>1,'message'=>'Success!','data'=> $bodyresponse]), 200);
                    }
                }catch(RequestException $e) {
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Access Token Wrong!','data'=> '']), 401);
                }
                return response()->json(new ValueMessage(['value'=>0,'message'=>'not get!','data'=> '']), 401);
            }
        }
    }

    //step 4
    public function setPassenger(Request $request)
    {
        $bookingsession=$this->checkSession(Auth::id());

        $validator = Validator::make($request->all(), [
            'contact_title' => 'required',
            'contact_first_name' => 'required',
            'contact_last_name' => 'required',
            'contact_country_code_phone' => 'required',
            'contact_area_code_phone' => 'required',
            'contact_remaining_phone_no' => 'required',
            'pax_details' => 'required'
        ]);
        if(! $bookingsession){
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Search Flight Schedule First!','data'=> '']), 401);
        }else if($bookingsession->depart_reference==null){
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Search Price First!','data'=> '']), 401);
        }else{
            if ($validator->fails()) {
                return response()->json(['error'=>$validator->errors()], 400);
            }else{
                $pax_details=$request->pax_details;
                $passangersession=FlightPassengerSession::where('id_flight_booking_session',$bookingsession->id)->get();
                foreach ($passangersession as $key => $value) {
                    $addonssession=FlightAddonsSession::where('id_flight_passenger_session',$value->id)->delete();
                }

                $passangersession=FlightPassengerSession::where('id_flight_booking_session',$bookingsession->id)->delete();
                foreach ($pax_details as $key => $value) {
                    $pax_data=[
                        'id_flight_booking_session' => $bookingsession->id,
                        "id_number" => $value['id_number'] ,
                        "title" => $value['title'] ,
                        "first_name" => $value['first_name'] ,
                        "last_name" => $value['last_name'] ,
                        "birth_date" => $value['birth_date'] ,
                        "gender" => $value['gender'] ,
                        "nationality" => $value['nationality'] ,
                        "birth_country" => $value['birth_country'] ,
                        "parent" => $value['parent'] ,
                        "type"=> $value['type'] ,
                    ];
                    if($value['passport_number']){
                        $pax_data['passport_number'] = $value['passport_number'] ;
                        $pax_data['passport_issued_country'] = $value['passport_issued_country'] ;
                        $pax_data['passport_issued_date'] = $value['passport_issued_date'] ;
                        $pax_data['passport_expired_date'] = $value['passport_expired_date'] ;
                    }
                    $passangersession=FlightPassengerSession::create($pax_data);
                }
                $passangersession=FlightPassengerSession::where('id_flight_booking_session',$bookingsession->id)->get();
                $bookingsession=FlightBookingSession::where('id_user',Auth::id())->update([
                    'contact_title' => $request->contact_title,
                    'contact_first_name' => $request->contact_first_name,
                    'contact_last_name' => $request->contact_last_name,
                    'contact_country_code_phone' => $request->contact_country_code_phone,
                    'contact_area_code' => $request->contact_area_code_phone,
                    'contact_remaining_phone_no' => $request->contact_remaining_phone_no,
                    'insurance' => $request->insurance,
                ]);
                return response()->json(new ValueMessage(['value'=>1,'message'=>'Set Passenger Data Success!','data'=> $passangersession]), 200);

            }
        }
    }

    //step 5
    public function getAirlineAddons(Request $request)
    {
        $bookingsession=$this->checkSession(Auth::id());
        $passangersession=FlightPassengerSession::where('id_flight_booking_session',$bookingsession->id)->get();
        if(! $bookingsession){
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Search Flight Schedule First!','data'=> '']), 401);
        }else if($bookingsession->depart_reference==null){
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Get Price First!','data'=> '']), 401);
        }else if ($passangersession->isEmpty()) {
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Set Passenger Data First!','data'=> '']), 401);
        }
        else{
            $userid=$this->username;
            $token=$this->checkLoginUser();
            $trip_type=$bookingsession->trip_type;
            $airline=$bookingsession->airline_id;
            $origin=$bookingsession->origin;
            $destination=$bookingsession->destination;
            $depart_date=$bookingsession->depart_date;
            $return_date=$bookingsession->return_date;
            $adult=$bookingsession->pax_adult;
            $child=$bookingsession->pax_child;
            $infant=$bookingsession->pax_infant;
            $depart_reference=$bookingsession->depart_reference;
            $return_reference=$bookingsession->return_reference;

            $pax_details=$passangersession;
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
                    'schDepart'=>$depart_reference,
                    'schReturn'=>$return_reference,
                    'contactTitle' => $bookingsession->contact_title,
                    'contactFirstName' => $bookingsession->contact_first_name,
                    'contactLastName' => $bookingsession->contact_last_name,
                    'contactCountryCodePhone' => $bookingsession->contact_country_code_phone,
                    'contactAreaCodePhone' => $bookingsession->contact_area_code,
                    'contactRemainingPhoneNo' => $bookingsession->contact_remaining_phone_no,
                    'insurance' => $bookingsession->insurance,
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

    //step 6
    public function getAirlineSeat(Request $request)
    {
        $bookingsession=$this->checkSession(Auth::id());
        $passangersession=FlightPassengerSession::where('id_flight_booking_session',$bookingsession->id)->get();
        if(! $bookingsession){
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Search Flight Schedule First!','data'=> '']), 401);
        }else if($bookingsession->depart_reference==null){
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Search Price First!','data'=> '']), 401);
        }else if ($passangersession->isEmpty()) {
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Set Passenger Data First!','data'=> '']), 401);
        }
        else{
            $userid=$this->username;
            $token=$this->checkLoginUser();
            $trip_type=$bookingsession->trip_type;
            $airline=$bookingsession->airline_id;
            $origin=$bookingsession->origin;
            $destination=$bookingsession->destination;
            $depart_date=$bookingsession->depart_date;
            $return_date=$bookingsession->return_date;
            $adult=$bookingsession->adult;
            $child=$bookingsession->child;
            $infant=$bookingsession->infant;
            $depart_reference=$bookingsession->depart_reference;
            $return_reference=$bookingsession->return_reference;

            foreach ($passangersession as $key => $value) {

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
                    'schDepart'=>$depart_reference,
                    'schReturn'=>$return_reference,
                    'contactTitle' => $bookingsession->contact_title,
                    'contactFirstName' => $bookingsession->contact_first_name,
                    'contactLastName' => $bookingsession->contact_last_name,
                    'contactCountryCodePhone' => $bookingsession->contact_country_code_phone,
                    'contactAreaCodePhone' => $bookingsession->contact_area_code,
                    'contactRemainingPhoneNo' => $bookingsession->contact_remaining_phone_no,
                    'insurance' => $bookingsession->insurance,
                    'paxDetails' => $pax_data 
                ];
                $response=$this->client->request(
                    'POST',
                    'airline/seat',
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

    //step 7
    public function setPassengerAddons(Request $request)
    {
        $bookingsession=$this->checkSession(Auth::id());
        $passangersession=FlightPassengerSession::where('id_flight_booking_session',$bookingsession->id)->get();

        $validator = Validator::make($request->all(), [
            'pax_details' => 'required'
        ]);
        if(! $bookingsession){
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Search Flight Schedule First!','data'=> '']), 401);
        }else if($bookingsession->depart_reference==null){
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Search Price First!','data'=> '']), 401);
        }else if ($passangersession->isEmpty()) {
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Search Addons First!','data'=> '']), 401);
        }else{

            if ($validator->fails()) {
                return response()->json(['error'=>$validator->errors()], 400);
            }else{
                $pax_details=$request->pax_details;

                foreach ($pax_details as $key => $value) {
                    $passangersession=FlightPassengerSession::where('id_flight_booking_session',$bookingsession->id)->where('id',$value['id'])->first();
                    $addonssession=FlightAddonsSession::where('id_flight_passenger_session',$passangersession->id)->delete();

                    foreach ($value['trip'] as $key => $value) {
                        $trip=FlightTripSession::where('id_flight_booking_session',$bookingsession->id)->where('sch_origin',$value['origin'])->where('sch_destination',$value['destination'])->first();
                        $addons=[
                            "id_flight_passenger_session" => $passangersession->id,
                            "id_flight_trip_session" => $trip->id,
                            "baggage_string" => $value['baggage'],
                            "seat" => $value['seat'],
                            "compartment" => $value['compartment'],
                            "meals" => json_encode($value['meals'])
                        ];
                        $addonssession=FlightAddonsSession::create($addons);
                    }
                }
                return response()->json(new ValueMessage(['value'=>1,'message'=>'Set Passenger Addons Success!','data'=> '']), 200);

            }
        }
    }

    //step 8
    public function setAirlineBooking(Request $request)
    {
        $bookingsession=$this->checkSession(Auth::id());
        $passangersession=FlightPassengerSession::where('id_flight_booking_session',$bookingsession->id)->get();
        $tripsession=FlightTripSession::where('id_flight_booking_session',$bookingsession->id)->get();
        foreach ($passangersession as $key => $value) {
            $addons[$key]=FlightAddonsSession::where('id_flight_passenger_session',$value->id)->get();
        }
        if(! $bookingsession){
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Search Flight Schedule First!','data'=> '']), 401);
        }else if($bookingsession->depart_reference==null){
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Search Price First!','data'=> '']), 401);
        }else if ($passangersession->isEmpty()) {
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Search Addons First!','data'=> '']), 401);
        }else if(!isset($addons)){
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Set Passanger Addons First!','data'=> '']), 401);
        }
        else{
            $userid=$this->username;
            $token=$this->checkLoginUser();
            $trip_type=$bookingsession->trip_type;
            $airline=$bookingsession->airline_id;
            $origin=$bookingsession->origin;
            $destination=$bookingsession->destination;
            $depart_date=$bookingsession->depart_date;
            $return_date=strval($bookingsession->return_date);
            $adult=strval($bookingsession->pax_adult);
            $child=strval($bookingsession->pax_child);
            $infant=strval($bookingsession->pax_infant);

            $departsession=FlightTripSession::where('type','depart')->where('id_flight_booking_session',$bookingsession->id)->get();
            if($departsession->isEmpty()){
                $depart_reference="";

            }else{
                $depart_reference=[];
            }
            foreach ($departsession as $key => $value) {
                $depart_reference[$key]=[
                    "airlineCode" => $value->airline_code,
                    "flightNumber"=> $value->flight_number,
                    "schOrigin"=> $value->sch_origin,
                    "schDestination"=> $value->sch_destination,
                    "detailSchedule"=> $value->detail_schedule,
                    "schDepartTime"=> str_replace(" ", "T", strval($value->sch_depart_time)),
                    "schArrivalTime"=> str_replace(" ", "T", strval($value->sch_arrival_time)),
                    "flightClass"=> $value->flight_class,
                    "garudaNumber"=> strval($value->garuda_number),
                    "garudaAvailability"=> strval($value->garuda_availability)
                ];
            }

            $returnsession=FlightTripSession::where('type','return')->where('id_flight_booking_session',$bookingsession->id)->get();

            if($returnsession->isEmpty()){
                $return_reference="";

            }else{
                $return_reference=[];
            }
            foreach ($returnsession as $key => $value) {
                $return_reference[$key]=[
                    "airlineCode" => $value->airline_code,
                    "flightNumber"=> $value->flight_number,
                    "schOrigin"=> $value->sch_origin,
                    "schDestination"=> $value->sch_destination,
                    "detailSchedule"=> $value->detail_schedule,
                    "schDepartTime"=> str_replace(" ", "T", strval($value->sch_depart_time)),
                    "schArrivalTime"=> str_replace(" ", "T", strval($value->sch_arrival_time)),
                    "flightClass"=> $value->flight_class,
                    "garudaNumber"=> $value->garuda_number,
                    "garudaAvailability"=> $value->garuda_availability
                ];
            }
            

            foreach ($passangersession as $key => $value) {

                $pax_data[$key]=[
                    "IDNumber" => $value['id_number'] ,
                    "title" => $value['title'] ,
                    "firstName" => $value['first_name'] ,
                    "lastName" => $value['last_name'] ,
                    "birthDate" => $value['birth_date'] ,
                    "gender" => $value['gender'] ,
                    "nationality" => $value['nationality'] ,
                    "birthCountry" => $value['birth_country'] ,
                    "parent" => strval($value['parent']),
                    "type"=> $value['type'] ,
                ];
                if($value['passport_number']){
                    $pax_data[$key]['passportNumber'] = $value['passport_number'] ;
                    $pax_data[$key]['passportIssuedCountry'] = $value['passport_issued_country'] ;
                    $pax_data[$key]['passportIssuedDate'] = $value['passport_issued_date'] ;
                    $pax_data[$key]['passportExpiredDate'] = $value['passport_expired_date'] ;
                }else{

                    $pax_data[$key]['passportNumber'] = "";
                    $pax_data[$key]['passportIssuedCountry'] = "" ;
                    $pax_data[$key]['passportIssuedDate'] = "";
                    $pax_data[$key]['passportExpiredDate'] = "";
                }
                $addons=FlightAddonsSession::where('id_flight_passenger_session',$value['id'])->with('flighttripsession')->get();
                foreach ($addons as $k => $v) {
                    $pax_data[$key]['addOns'][$k] = [
                        "aoOrigin" => $v->flighttripsession->sch_origin,
                        "aoDestination" => $v->flighttripsession->sch_destination,
                        "baggageString" => strval($v->baggage_string),
                        "seat" => strval($v->seat),
                        "compartment" => strval($v->compartment),
                        "meals" => json_decode($v->meals) 
                    ] ;
                    
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

                    'schDeparts'=>$depart_reference,
                    'schReturns'=>$return_reference,
                    
                    'contactTitle' => $bookingsession->contact_title,
                    'contactFirstName' => $bookingsession->contact_first_name,
                    'contactLastName' => $bookingsession->contact_last_name,
                    'contactCountryCodePhone' => $bookingsession->contact_country_code_phone,
                    'contactAreaCodePhone' => $bookingsession->contact_area_code,
                    'contactRemainingPhoneNo' => $bookingsession->contact_remaining_phone_no,
                    'insurance' => strval($bookingsession->insurance),
                    'searchKey' => "",
                    'paxDetails' => $pax_data 
                ];
                $response=$this->client->request(
                    'POST',
                    'airline/booking',
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

                    $this->setBooking();

                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Success!','data'=> $bodyresponse]), 200);
                }
            }catch(RequestException $e) {
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Access Token Wrong!','data'=> '']), 401);
            }
            return response()->json(new ValueMessage(['value'=>0,'message'=>'not get!','data'=> '']), 401);
        
        }
    }

    public function setBooking($value='')
    {
        
    }

}
