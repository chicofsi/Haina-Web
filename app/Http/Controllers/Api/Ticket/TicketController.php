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
use App\Models\FlightDetailsSession;

use App\Models\FlightBooking;
use App\Models\FlightTrip;
use App\Models\FlightPassenger;
use App\Models\FlightAddons;
use App\Models\FlightBookingDetails;
use App\Models\Passengers;


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
        $token=DarmawisataSession::where('id_user',Auth::id())->whereRaw(' created BETWEEN DATE_SUB(now() , INTERVAL 15 MINUTE) AND now()')->first();
        if($token){
            return $token->access_token;
        }else{
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
            $passenger=FlightPassengerSession::where('id_flight_booking_session',$value->id)->delete();

            $detailssession=FlightDetailsSession::where('id_flight_booking_session',$value->id)->get();
            foreach ($detailssession as $k => $v) {
                $flighttrip=FlightTripSession::where('id_flight_details_session', $v->id)->delete();
            }
            $detailssession=FlightDetailsSession::where('id_flight_booking_session',$value->id)->delete();


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
                        $detailssession=FlightDetailsSession::where('id_flight_booking_session',$bookingsession->id)->get();
                        foreach ($detailssession as $key => $value) {
                            FlightTripSession::where('id_flight_details_session',$value->id)->delete();
                        }

                        FlightDetailsSession::where('id_flight_booking_session',$bookingsession->id)->delete();
                        $detailssession=FlightDetailsSession::create([
                            "id_flight_booking_session" => $bookingsession->id,
                            "type" => "depart",
                            "airline_code" => $request->depart['airline_code'],
                            "depart_from" => $request->depart['origin'],
                            "depart_to" => $request->depart['destination'],
                            "depart_date" => $request->depart['depart_time'],
                            "arrival_date" => $request->depart['arrival_time'],
                        ]);
                        foreach ($request->depart['flight_detail'] as $key => $value) {
                            $tripdepart=FlightTripSession::create([
                                "id_flight_details_session" => $detailssession->id,
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

                            $detailssession=FlightDetailsSession::create([
                                "id_flight_booking_session" => $bookingsession->id,
                                "type" => "return",
                                "airline_code" => $request->depart['airline_code'],
                                "depart_from" => $request->depart['origin'],
                                "depart_to" => $request->depart['destination'],
                                "depart_date" => $request->depart['depart_time'],
                                "arrival_date" => $request->depart['arrival_time'],
                            ]);
                            foreach ($request->return['flight_detail'] as $key => $value) {
                                $tripdepart=FlightTripSession::create([
                                    "id_flight_details_session" => $detailssession->id,
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
                        $responsee=$this->client->request(
                            'POST',
                            'airline/seat',
                            [
                                'form_params' => $body,
                                'on_stats' => function (TransferStats $stats) use (&$url) {
                                    $url = $stats->getEffectiveUri();
                                }
                            ]  
                        );

                        $bodyresponsee=json_decode($responsee->getBody()->getContents());


                        DarmawisataRequest::insert(
                            [
                                'request'=>json_encode($body),
                                'response'=>json_encode($bodyresponsee),
                                'status'=>$bodyresponsee->status,
                                'url'=>$url,
                                'response_code'=>$responsee->getStatusCode()
                            ]
                        );
                        if($bodyresponsee->status=="FAILED"){
                            if($bodyresponsee->respMessage=="member authentication failed"){
                                return response()->json(new ValueMessage(['value'=>0,'message'=>'Access Token Wrong!','data'=> '']), 401);
                            }else if($bodyresponsee->respMessage=="airline access code is empty or not valid"){
                                return response()->json(new ValueMessage(['value'=>0,'message'=>'Access Code Wrong!','data'=> $bodyresponsee->airlineAccessCode]), 401);;
                            }
                        }else{
                            if($bodyresponsee->seatAddOns==null){
                                foreach ($bodyresponse->addOns as $k => $v) {
                                    if($value->origin==$v->origin && $value->destination==$v->destination){
                                        $bodyresponse->addOns[$k]->seatInfos=null;
                                    }
                                }
                            }else{
                                foreach ($bodyresponsee->seatAddOns as $key => $value) {
                                    foreach ($bodyresponse->addOns as $k => $v) {
                                        if($value->origin==$v->origin && $value->destination==$v->destination){
                                            $bodyresponse->addOns[$k]->seatInfos=$value->infos;
                                        }
                                    }
                                }
                            }
                            
                            return response()->json(new ValueMessage(['value'=>1,'message'=>'Success!','data'=> $bodyresponse->addOns]), 200);
                        }
                    }catch(RequestException $e) {
                        return response()->json(new ValueMessage(['value'=>0,'message'=>'Access Token Wrong!','data'=> '']), 401);
                    }
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
                    $detailssession=FlightDetailsSession::where('id_flight_booking_session',$bookingsession->id)->get();

                    foreach ($detailssession as $k => $v) {
                        foreach ($value['trip'] as $key => $value) {
                            $trip=FlightTripSession::where('id_flight_details_session',$v->id)->where('sch_origin',$value['origin'])->where('sch_destination',$value['destination'])->first();
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
        $detailssession=FlightDetailsSession::where('id_flight_booking_session',$bookingsession->id)->get();
        foreach ($detailssession as $key => $value) {
            $tripsession[$value->type]=FlightTripSession::where('id_flight_details_session',$value->id)->get();
        }
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

            if($tripsession['depart']->isEmpty()){
                $depart_reference=null;
            }else{
                $depart_reference=[];
            }
            foreach ($tripsession['depart'] as $key => $value) {
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

            $returndetails=FlightDetailsSession::where('id_flight_booking_session',$bookingsession->id)->where('type','return')->get();

            if($returndetails->isEmpty()){
                $return_reference=null;
            }else{
                $return_reference=[];
                foreach ($tripsession['return'] as $key => $value) {
                    $return_reference[$key]=[
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

                    $this->setBooking($bodyresponse);

                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Success!','data'=> $bodyresponse]), 200);
                }
            }catch(RequestException $e) {
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Access Token Wrong!','data'=> '']), 401);
            }
            return response()->json(new ValueMessage(['value'=>0,'message'=>'not get!','data'=> '']), 401);
        }
    }

    public function setBooking($response)
    {
        $bookingsession=$this->checkSession(Auth::id());
        $passangersession=FlightPassengerSession::where('id_flight_booking_session',$bookingsession->id)->get();
        $detailssession=FlightDetailsSession::with('flighttripsession')->where('id_flight_booking_session',$bookingsession->id)->get();

        $flightbooking=FlightBooking::create([
            "order_id"=> $this->generateOrderId(),
            "id_user" => Auth::id(),
            "trip_type" =>$bookingsession->trip_type,
            "customer_email" => Auth::user()->email,
            "amount" => $response->ticketPrice,
            "status" => "pending",
            "booking_date" => date("Y-m-d h:m:s")
        ]);

        foreach ($detailssession as $key => $value) {
            $flightbookingdetails = FlightBookingDetails::create([
                "id_flight_book" => $flightbooking->id,
                "airline_code" => $value->airline_code,
                "depart_from" => $value->depart_from, 
                "depart_to" => $value->depart_to, 
                "depart_date" => $value->depart_date, 
                "arrival_date" => $value->arrival_date, 
            ]);
            foreach ($value->flighttripsession as $k => $val) {
                $flighttripsession=FlightTripSession::where('id',$val->id)->with('flightaddonssession')->first();
                $flighttrip=FlightTrip::create([
                    "id_flight_booking_detail" => $flightbookingdetails->id,
                    "airline_code" => $val->airline_code,
                    "flight_number" => $val->flight_number,
                    "origin" => $val->sch_origin,
                    "destination" => $val->sch_destination,
                    "detail_schedule" => $val->detail_schedule,
                    "depart_time" => $val->sch_depart_time,
                    "arrival_time" => $val->sch_arrival_time,
                    "flight_class" => $val->flight_class,
                    "garuda_number" => $val->garuda_number,
                    "garuda_availability" => $val->garuda_availability
                ]);

            }

        }
        foreach ($passangersession as $key => $value) {
            $passenger=Passengers::updateOrCreate([
                "user_id" => Auth::id(),
                "first_name" => $value->first_name,
                "last_name" => $value->last_name,
                "id_number" => $value->id_number
            ],
            [
                "title" => $value->title,
                "date_of_birth" => $value->birth_date,
                "gender" => $value->gender,
                "type" => $value->type,
                "nationality" => $value->nationality,
                "birth_country" => $value->birth_country,
                "parent" => $value->parent
            ]);
            if($value->passport_number){
                $passengerpassport=PassengerPassport::updateOrCreate([
                    "id_passenger" => $flightpassenger->id,
                    "passport_number" => $value->passport_number,
                ],
                [
                    "passport_issued_date" => $value->passport_issued_date,
                    "passport_issued_country" => $value->passport_issued_country,
                    "passport_expired_date" => $value->passport_expired_date
                ]);
            }

            $flightbookingdetails = FlightBookingDetails::where('id_flight_book',$flightbooking->id)->get();
            foreach ($flightbookingdetails as $key_details => $value_details) {
                $flighttrip=FlightTrip::where('id_flight_booking_detail',$value_details->id)->with('flightaddonssession')->get();
                foreach ($flighttrip as $key_trip => $value_trip) {
                    $flightpassenger=FlightPassenger::create([
                        "id_passenger" => $passenger->id,
                        "id_flight_trip" => $value_trip->id
                    ]);
                    // $flightaddons = FlightAddons::create([
                    //     "id_flight_passenger" => $flightpassenger,
                    //     ba
                    // ])
                }
            }


            
        }

        

    }

    function generateOrderId() {
        $randomString = '';
        do{
            $length = 10;
            $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $charactersLength = strlen($characters);
            for ($i = 0; $i < $length; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }

        }while (FlightBooking::where('order_id',$randomString)->first());
        
        return $randomString;
    }


}
