<?php

namespace App\Http\Controllers\Api\Hotel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

use GuzzleHttp\Client;
use GuzzleHttp\TransferStats;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;

use App\Models\HotelDarma;
use App\Models\DarmawisataSession;
use App\Models\DarmawisataRequest;
use App\Models\HotelDarmaBookingSession;
use App\Models\HotelDarmaBookingRoomReq;
use App\Models\HotelDarmaBookingPaxes;

use App\Http\Resources\ValueMessage;

class HotelDarmaController extends Controller
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
            }
            else{
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
        $token=DarmawisataSession::where('id_user',Auth::id())->first();
        if($token){
            $token=$token->access_token;
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
        }else{
            return $this->login();

        }
        
    }

    public function deleteSession($id_user)
    {
        $hotel_booking=HotelDarmaBookingSession::where('user_id',$id_user)->get();
        foreach ($hotel_booking as $key => $value) {

            $room_req = HotelDarmaBookingRoomReq::where('id_booking_session', $value->id)->get();

            foreach ($room_req as $room_key => $room_value){
                $paxes = HotelDarmaBookingPaxes::where('id_room_req', $room_value->id)->delete();
            }

            $room_req = HotelDarmaBookingRoomReq::where('id_booking_session', $value->id)->delete();


        }
        $hotel_booking=HotelDarmaBookingSession::where('user_id',$id_user)->delete();    
    }

    public function getCountry(Request $request){
        $userid=$this->username;
        $token=$this->checkLoginUser();
        $body=[
            'userID'=>$userid,
            'accessToken'=>$token
        ];
        try {
            $response=$this->client->request(
                'POST',
                'Hotel/Country',
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
            }
            else{
                return response()->json(new ValueMessage(['value'=>1,'message'=>'Get Country List Success!','data'=> $bodyresponse->countries]), 200);
            }
        }catch(RequestException $e) {
            dd($e);
            return;
        }
    }

    public function getPassport(Request $request){
        $userid=$this->username;
        $token=$this->checkLoginUser();
        $body=[
            'userID'=>$userid,
            'accessToken'=>$token
        ];
        try {
            $response=$this->client->request(
                'POST',
                'Hotel/Passport',
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
            }
            else{
                return response()->json(new ValueMessage(['value'=>1,'message'=>'Get Passport List Success!','data'=> $bodyresponse->passports]), 200);
            }
        }catch(RequestException $e) {
            dd($e);
            return;
        }
    }

    public function getCity(Request $request){
        $userid = $this->username;
        $token = $this->checkLoginUser();
        $country = $request->country_id;
        $cityfilter = $request->city_filter;

        $body = [
            'userID'=>$userid,
            'accessToken'=>$token,
            'countryID'=>$country,
            'cityNameFilter'=>$cityfilter
        ];

        try {
            $response=$this->client->request(
                'POST',
                'Hotel/City5',
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
            }
            else{
                return response()->json(new ValueMessage(['value'=>1,'message'=>'Get City List Success!','data'=> $bodyresponse->cities]), 200);
            }
        }catch(RequestException $e) {
            dd($e);
            return;
        }
    }

    public function getAllCityCountry(Request $request){
        $userid=$this->username;
        $token=$this->checkLoginUser();
        $body=[
            'userID'=>$userid,
            'accessToken'=>$token
        ];
        try {
            $response=$this->client->request(
                'POST',
                'Hotel/AllCountryAllCity5',
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
            }
            else{
                return response()->json(new ValueMessage(['value'=>1,'message'=>'Get Country List Success!','data'=> $bodyresponse->countries]), 200);
            }
        }catch(RequestException $e) {
            dd($e);
            return;
        }
    }

    //Search #1 - Search All Hotel in City

    public function searchHotel(Request $request){

        $validator = Validator::make($request->all(), [
            'pax_passport' => 'required',
            'country_id' => 'required',
            'city_id' => 'required',
            'check_in_date' => 'required',
            'check_out_date' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }
        else{
            $this->deleteSession(Auth::id());

            $userid = $this->username;
            $token = $this->checkLoginUser();
            $passport = $request->pax_passport;
            $country = $request->country_id;
            $city = $request->city_id;
            $checkin = $request->check_in_date;
            $checkout = $request->check_out_date;
            $room_request = [
                'roomType' => "Single",
                'isRequestChildBed' => false,
                'childNum' => 0,
                'childAges' => []
            ];

            try{
                $body = [
                    'userID' => $userid,
                    'accessToken' => $token,
                    'paxPassport' => $passport,
                    'countryID' => $country,
                    'cityID' => $city,
                    'checkInDate' => $checkin,
                    'checkOutDate' => $checkout,
                    'roomRequest' => array($room_request)
                ];
                $response=$this->client->request(
                    'POST',
                    'Hotel/Search5',
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
                    }else if($bodyresponse->respMessage=="wrong format request or null mandatory data"){
                        return response()->json(new ValueMessage(['value'=>0,'message'=>'Data is incomplete!','data'=> '']), 403);
                    }
                }
                else{
                    $body = [
                        'user_id' => Auth::id(),
                        'pax_passport' => $passport,
                        'country_id' => $country,
                        'city_id' => $city,
                        'check_in_date' => $checkin,
                        'check_out_date' => $checkout
                    ];
    
                    $booksession = HotelDarmaBookingSession::create($body);
    
                    //foreach($bodyresponse->roomRequest as $key => $value){
                    //    $roomreq_session[$key] = $value;
                    //}
    
                    //$roomreq_session['id_booking_session'] = $booksession->id;

                    $roomreq_session = [
                        'id_booking_session' => $booksession->id,
                        'room_type' => $bodyresponse->roomRequest[0]->roomType,
                        'is_request_child_bed' => $bodyresponse->roomRequest[0]->isRequestChildBed,
                        'child_num' => $bodyresponse->roomRequest[0]->childNum,
                        'child_age' => implode(",",array($bodyresponse->roomRequest[0]->childAges))
                    ];
    
                    $roomrequestdata = HotelDarmaBookingRoomReq::create($roomreq_session);
                    
                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Success!','data'=> $bodyresponse]), 200);
    
                }
    
            }
            catch(RequestException $e){
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Access Token Wrong!','data'=> '']), 401);
            }
            return response()->json(new ValueMessage(['value'=>0,'message'=>'not get!','data'=> '']), 401);
        }
           
    }

    //Search #1.5 - Check Session

    public function checkSession($id_user){
        $bookingsession=HotelDarmaBookingSession::where('user_id',$id_user)->first();
        if($bookingsession){
            return $bookingsession;
        }else{
            return false;
        }
    }

    //Search #2 - Search Available Rooms in Hotel
    public function searchRoom(Request $request){
        $bookingsession=$this->checkSession(Auth::id());
        if(! $bookingsession){
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Search Hotel First!','data'=> '']), 401);
        }else{
            $validator = Validator::make($request->all(), [
                'hotel_id' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json(['error'=>$validator->errors()], 400);
            }
            else{
                $userid=$this->username;
                $token=$this->checkLoginUser();
                $passport = $bookingsession->pax_passport;
                $country = $bookingsession->country_id;
                $city = $bookingsession->city_id;
                $checkin = $bookingsession->check_in_date;
                $checkout = $bookingsession->check_out_date;

                $hotelid = $request->hotel_id;

                $room_req_data = HotelDarmaBookingRoomReq::select('room_type', 'is_request_child_bed', 'child_num', 'child_age')->where('id_booking_session',$bookingsession->id)->first();

                if($room_req_data['room_type'] == 0){
                    $roomtype = "Single";
                }
                else if($room_req_data['room_type'] == 1){
                    $roomtype = "Double";
                }
                else if($room_req_data['room_type'] == 2){
                    $roomtype = "Twin";
                }
                else if($room_req_data['room_type'] == 3){
                    $roomtype = "Triple";
                }
                else if($room_req_data['room_type'] == 4){
                    $roomtype = "Quad";
                }
                

                $room_request = [
                    'roomType' => $roomtype,
                    'isRequestChildBed' => $room_req_data['is_request_child_bed'],
                    'childNum' => $room_req_data['child_num'],
                    'childAges' => explode(',', $room_req_data['child_age'])
                ];

                try{

                    $body = [
                        'userID' => $userid,
                        'accessToken' => $token,
                        'paxPassport' => $passport,
                        'countryID' => $country,
                        'cityID' => $city,
                        'checkInDate' => $checkin,
                        'checkOutDate' => $checkout,
                        'hotelID' => $hotelid,
                        'roomRequest' => array($room_request)
                    ];
                    $response=$this->client->request(
                        'POST',
                        'Hotel/AvailableRooms5',
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
                        }else if($bodyresponse->respMessage=="wrong format request or null mandatory data"){
                            return response()->json(new ValueMessage(['value'=>0,'message'=>'Data is incomplete!','data'=> '']), 403);
                        }
                    }
                    else{
                        $bookingsession=HotelDarmaBookingSession::where('user_id',Auth::id())->update([
                            'hotel_id'=>$hotelid,
                            'internal_code'=>$bodyresponse->hotelInfo->internalCode
                        ]);

                        unset($bodyresponse->hotelInfo->nearbyProperty);
                        
                        return response()->json(new ValueMessage(['value'=>1,'message'=>'Success!','data'=> $bodyresponse]), 200);
                        //
                    }

                }catch(RequestException $e) {
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Access Token Wrong!','data'=> '']), 401);
                }
                return response()->json(new ValueMessage(['value'=>0,'message'=>'not get!','data'=> '']), 401);

            }
        }

    }

    //Search #3 - Show Price & Policy

    public function showPricePolicy(Request $request){
        $bookingsession=$this->checkSession(Auth::id());
        if(! $bookingsession){
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Search Hotel First!','data'=> '']), 401);
        }else{

            $validator = Validator::make($request->all(), [
                'room_id' => 'required',
                'breakfast' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json(['error'=>$validator->errors()], 400);
            }
            else{
                $userid=$this->username;
                $token=$this->checkLoginUser();
                $passport = $bookingsession->pax_passport;
                $country = $bookingsession->country_id;
                $city = $bookingsession->city_id;
                $checkin = $bookingsession->check_in_date;
                $checkout = $bookingsession->check_out_date;
                $hotelid = $bookingsession->hotel_id;
                $internalcode = $bookingsession->internal_code;

                $roomid = $request->room_id;
                $breakfast = $request->breakfast;

                $room_req_data = HotelDarmaBookingRoomReq::select('room_type', 'is_request_child_bed', 'child_num', 'child_age')->where('id_booking_session',$bookingsession->id)->first();

                if($room_req_data['room_type'] == 0){
                    $roomtype = "Single";
                }
                else if($room_req_data['room_type'] == 1){
                    $roomtype = "Double";
                }
                else if($room_req_data['room_type'] == 2){
                    $roomtype = "Twin";
                }
                else if($room_req_data['room_type'] == 3){
                    $roomtype = "Triple";
                }
                else if($room_req_data['room_type'] == 4){
                    $roomtype = "Quad";
                }

                $room_request = [
                    'roomType' => $roomtype,
                    'isRequestChildBed' => $room_req_data['is_request_child_bed'],
                    'childNum' => $room_req_data['child_num'],
                    'childAges' => explode(',', $room_req_data['child_age'])
                ];


                try{
                    $body = [
                        'userID' => $userid,
                        'accessToken' => $token,
                        'paxPassport' => $passport,
                        'countryID' => $country,
                        'cityID' => $city,
                        'checkInDate' => $checkin,
                        'checkOutDate' => $checkout,
                        'hotelID' => $hotelid,
                        'roomID' => $roomid,
                        'internalCode' => $internalcode,
                        'breakfast' => $breakfast,
                        'roomRequest' => array($room_request)
                    ];

                    $response=$this->client->request(
                        'POST',
                        'Hotel/PriceAndPolicyInfo',
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
                        }else if($bodyresponse->respMessage=="wrong format request or null mandatory data"){
                            return response()->json(new ValueMessage(['value'=>0,'message'=>'Data is incomplete!','data'=> '']), 403);
                        }
                    }
                    else{
                        $bookingsession=HotelDarmaBookingSession::where('user_id',Auth::id())->update([
                            'room_id'=>$roomid,
                            'breakfast'=>$breakfast
                        ]);
                        
                        return response()->json(new ValueMessage(['value'=>1,'message'=>'Success!','data'=> $bodyresponse]), 200);
                    }

                }
                catch(RequestException $e) {
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Access Token Wrong!','data'=> '']), 401);
                }
                return response()->json(new ValueMessage(['value'=>0,'message'=>'not get!','data'=> '']), 401);
            }

        }

        


    }

}