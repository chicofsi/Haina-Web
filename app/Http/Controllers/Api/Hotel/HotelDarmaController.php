<?php

namespace App\Http\Controllers\Api\Hotel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

use GuzzleHttp\Client;
use GuzzleHttp\TransferStats;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;

use App\Models\City;
use App\Models\HotelDarma;
use App\Models\HotelDarmaBooking;
use App\Models\HotelDarmaPayment;
use App\Models\HotelDarmaCommonFacility;
use App\Models\HotelDarmaFacilities;
use App\Models\HotelDarmaFacilitiesGroup;
use App\Models\HotelDarmaFacilitiesList;
use App\Models\HotelDarmaRoomFacilitiesList;
use App\Models\HotelDarmaRoomFacilities;
use App\Models\HotelDarmaRoom;
use App\Models\HotelDarmaImage;
use App\Models\DarmawisataSession;
use App\Models\DarmawisataRequest;
use App\Models\HotelDarmaBookingSession;
use App\Models\HotelDarmaBookingRoomReq;
use App\Models\HotelDarmaBookingPaxes;
use App\Models\HotelDarmaPaxesList;
use App\Models\HotelDarmaRequestList;
use App\Models\PaymentMethod;
use App\Models\PaymentMethodCategory;
use App\Models\PersonalAccessToken;

use App\Http\Controllers\Api\Notification\NotificationController;

use App\Http\Resources\ValueMessage;
use App\Http\Resources\HotelDarmaBookingResource;

use DateTime;


//use Intervention\Image\ImageManagerStatic as Image;


class HotelDarmaController extends Controller
{

    public function __construct()
    {
        $this->username="HAYQ18MKPK";
        $this->password="HAQQQ8MKPK";
        $this->client = new Client([
            'verify' => false,
            'base_uri' => 'https://61.8.74.42:7080/h2h/',
            'timeout'  => 300.0
        ]);
    }

    public function login($user_id)
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
                $session=DarmawisataSession::where('id_user',$user_id)->delete();
                $session=DarmawisataSession::create([
                    'access_token'=>$bodyresponse->accessToken,
                    'id_user'=>$user_id
                ]);
                return $bodyresponse->accessToken;
            }
        }catch(RequestException $e) {
            return;
        }
    }

    public function checkLoginUser($user_id)
    {
        $token=DarmawisataSession::where('id_user',$user_id)->whereRaw(' created BETWEEN DATE_SUB(now() , INTERVAL 15 MINUTE) AND now()')->first();
        if($token){
            return $token->access_token;
        }else{
            DarmawisataSession::where('id_user',$user_id)->delete();
            return $this->login($user_id);
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
        $token=$this->checkLoginUser(Auth::id());
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

    public function getIndoCities(){
        $cities = City::all();

        foreach($cities as $key=>$value){
            if($value->image == null){
                $value->image = "https://images.bisnis-cdn.com/posts/2017/12/02/714525/nhantasari191117-1.jpg";
            }
        }

        return response()->json(new ValueMessage(['value'=>1,'message'=>'Get Passport List Success!','data'=> $cities]), 200);
    }

    public function getPassport(Request $request){
        $userid=$this->username;
        $token=$this->checkLoginUser(Auth::id());
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
        $token = $this->checkLoginUser(Auth::id());
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
        $token=$this->checkLoginUser(Auth::id());
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

    public function getImages($hotel_id){
        $userid=$this->username;
        $token=$this->checkLoginUser(Auth::id());
        
        $body = [
            'userID'=>$userid,
            'accessToken'=>$token,
            'hotelID'=>$hotel_id
        ];

        try{
            $response=$this->client->request(
                'POST',
                'Hotel/Images5',
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
                    $hotel = HotelDarma::where('id_darma', $hotel_id)->first();
                    //return $hotel;
                    $hotelimagecheck = HotelDarmaImage::where('hotel_id', $hotel['id'])->first();

                    if(! $hotelimagecheck){
                        foreach($bodyresponse->images as $key => $value){

                            //fungsi intervention image
                            //$filename = str_replace(' ','-', 'hotel_'.$hotel['name'].'_'.substr($value, -1));
                            //Image::make($value)->save(public_path('hotel/' . $filename));
                            //
                            $hotelimage = [
                                'hotel_id' => $hotel['id'],
                                'image' => $value,
                                'path' => ''
                                //'path' => "hotel/".$filename
                            ];

                            $newimage = HotelDarmaImage::create($hotelimage);
                        }
                    }

                    $firstimage = HotelDarmaImage::where('hotel_id', $hotel['id'])->first();

                    return $firstimage;

                }
        }
        catch(RequestException $e){
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Data not Get!','data'=> '']), 401);
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
            $token = $this->checkLoginUser(Auth::id());
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

                    /*
                    foreach($bodyresponse->hotels as $key => $value){
                        $hotel = HotelDarma::where('id_darma', $value->ID)->first();

                        if(!$hotel){
                            $hoteldata = [
                                'hotel_name' => $value->name,
                                'hotel_address' => $value->address,
                                'hotel_phone' => $value->phone, 
                                'city_id' => $request->city_id, 
                                'hotel_website' => $value->website, 
                                'hotel_email' => $value->email, 
                                'hotel_rating' => $value->rating, 
                                'id_darma' => $value->ID
                            ];

                            $newhotel = HotelDarma::create($hoteldata);
                        }

                        $image = $this->getImages($value->ID);

                        $value->image = $image; 
                    }
                    */
                    
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

    //Search Directly by Hotel Name
    public function searchByHotelName(Request $request){
        $validator = Validator::make($request->all(), [
            'hotel_name' => 'required',
            'check_in_date' => 'required',
            'check_out_date' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }
        else{
            $this->deleteSession(Auth::id());

            $userid = $this->username;
            $token = $this->checkLoginUser(Auth::id());
            $room_request = [
                'roomType' => "Single",
                'isRequestChildBed' => false,
                'childNum' => 0,
                'childAges' => []
            ];


            try{
                $body = [
                    'hotelNameFilter' => $request->hotel_name,
                    'userID' => $userid,
                    'accessToken' => $token,
                    'checkInDate' => $request->check_in_date,
                    'checkOutDate' => $request->check_out_date,
                    'roomRequest' => array($room_request)
                ];

                $response=$this->client->request(
                    'POST',
                    'Hotel/HotelList5',
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
                        }else if($bodyresponse->respMessage=="hotel not available"){
                            return response()->json(new ValueMessage(['value'=>0,'message'=>'Hotel Unavailable!','data'=> '']), 401);
                        }else if($bodyresponse->respMessage=="no room found"){
                            return response()->json(new ValueMessage(['value'=>0,'message'=>'No Room Found!','data'=> '']), 401);
                        }
                    }
                    else{
                        $body = [
                            'user_id' => Auth::id(),
                            'pax_passport' => "ID",
                            'country_id' => "ID",
                            'check_in_date' => $request->check_in_date,
                            'check_out_date' => $request->check_out_date
                        ];
        
                        $booksession = HotelDarmaBookingSession::create($body);

                        $roomreq_session = [
                            'id_booking_session' => $booksession->id,
                            'room_type' => 0,
                            'is_request_child_bed' => 0,
                            'child_num' => 0,
                        ];

                        $roomrequestdata = HotelDarmaBookingRoomReq::create($roomreq_session);

                        return response()->json(new ValueMessage(['value'=>1,'message'=>'Success!','data'=> $bodyresponse]), 200);

                    }
            }catch(RequestException $e) {
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Access Token Wrong!','data'=> '']), 401);
            }
            return response()->json(new ValueMessage(['value'=>0,'message'=>'not get!','data'=> '']), 401);


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
                $token=$this->checkLoginUser(Auth::id());
                $passport = $bookingsession->pax_passport;
                $country = $bookingsession->country_id;
                //$city = $request->city_id ?? $bookingsession->city_id;
                $checkin = $bookingsession->check_in_date;
                $checkout = $bookingsession->check_out_date;

                if($request->city_id != null){
                    $update_city = HotelDarmaBookingSession::where('id', $bookingsession->id)->update([
                        'city_id' => $request->city_id
                    ]);

                    $city = $request->city_id;
                }
                else{
                    $city = $bookingsession->city_id;
                }

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
                        }else if($bodyresponse->respMessage=="hotel not available"){
                            return response()->json(new ValueMessage(['value'=>0,'message'=>'Hotel Unavailable!','data'=> '']), 401);
                        }else if($bodyresponse->respMessage=="no room found"){
                            return response()->json(new ValueMessage(['value'=>0,'message'=>'No Room Found!','data'=> '']), 401);
                        }
                    }
                    else{
                        $bookingsession=HotelDarmaBookingSession::where('user_id',Auth::id())->update([
                            'hotel_id'=>$hotelid,
                            'internal_code'=>$bodyresponse->hotelInfo->internalCode
                        ]);


                        unset($bodyresponse->hotelInfo->nearbyProperty);

                        $hotel = HotelDarma::where('id_darma', $hotelid)->first();

                        if(!$hotel){
                            $hoteldata = [
                                'hotel_name' => $bodyresponse->hotelInfo->name,
                                'hotel_address' => $bodyresponse->hotelInfo->address,
                                'hotel_phone' => $bodyresponse->hotelInfo->phone, 
                                'city_id' => $bodyresponse->cityID, 
                                'hotel_website' => $bodyresponse->hotelInfo->website, 
                                'hotel_email' => $bodyresponse->hotelInfo->email, 
                                'hotel_rating' => $bodyresponse->hotelInfo->rating, 
                                'hotel_long' => $bodyresponse->hotelInfo->longitude, 
                                'hotel_lat' => $bodyresponse->hotelInfo->latitude, 
                                'id_darma' => $bodyresponse->hotelInfo->ID
                            ];

                            $newhotel = HotelDarma::create($hoteldata);
                        }

                        foreach($bodyresponse->hotelInfo->rooms as $key => $value){
                            $room = HotelDarmaRoom::where('id_darma_room', $value->ID)->first();

                            if(!$room){
                                
                                if(strpos($value->name, 'Twin') !== false ){
                                    $roomtype = 3;
                                }
                                else if(strpos($value->name, 'Double') !== false ){
                                    $roomtype = 2;
                                }
                                else{
                                    $roomtype = 1;
                                } 

                                $hotel = HotelDarma::where('id_darma', $hotelid)->first();
    
                                $newRoomData = [
                                    'hotel_id' => $hotel->id,
                                    'room_name' => $value->name,
                                    'room_type_id' => $roomtype,
                                    'room_image' => $value->image,
                                    'room_price' => $value->price,
                                    'breakfast' => $value->breakfast,
                                    'id_darma_room' => $value->ID
                                ];

                                $newRoom = HotelDarmaRoom::create($newRoomData);
                            }
                        }

                        $image = $this->getImages($hotelid);
                        /*
                        if($bodyresponse->hotelInfo->facilities != null){
                            
                            foreach($bodyresponse->hotelInfo->facilities as $key => $value){

                                $facility = [
                                    'name' => $value
                                ];
    
                                $hotel = HotelDarma::where('id_darma', $hotelid)->first();
                                $checkfacility = HotelDarmaFacilitiesList::where('name',$value)->first();
    
                                if(!$checkfacility){
                                    $newFacility = HotelDarmaFacilitiesList::create($facility);
    
                                }
                                
                                $checkfacility = HotelDarmaFacilitiesList::where('name',$value)->first();
                                $hotel->facilities()->attach($checkfacility->id);
    
                            }
                            
                        }
                        */

                        $common_facility = [];

                        foreach($bodyresponse->hotelInfo->facility as $key => $value){

                            $checkfacility = HotelDarmaFacilitiesGroup::where('name', $value->facilityGroupName)->first();

                            if($checkfacility){
                                $value->icon = $checkfacility['icon'];
                            }

                            foreach($value->facilities as $facility => $facility_name){
                                $checkcommon = HotelDarmaCommonFacility::where('name', $facility_name)->first();
                                //return($checkcommon);
                                if( $checkcommon){
                                    array_push($common_facility, $checkcommon);
                                }
                            }

                        }

                        $bodyresponse->hotelInfo->common_facility = $common_facility;


                        foreach($bodyresponse->hotelInfo->rooms as $key => $value){
                            foreach((array) $value->facilites as $key_room => $value_room){

                                $roomfacility = [
                                    'name' => $value_room
                                ];

                                $room = HotelDarmaRoom::where('id_darma_room', $value->ID)->first();
                                $checkfacility = HotelDarmaRoomFacilitiesList::where('name', $value_room)->first();

                                if(!$checkfacility){
                                    $newFacility = HotelDarmaRoomFacilitiesList::create($roomfacility);
                                }

                                $checkfacility = HotelDarmaRoomFacilitiesList::where('name',$value_room)->first();
                                $room->facilities()->attach($checkfacility->id);

                            }
                        }

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
                $token=$this->checkLoginUser(Auth::id());
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
                        }else if($bodyresponse->respMessage=="no room found"){
                            return response()->json(new ValueMessage(['value'=>0,'message'=>'No Room Found!','data'=> '']), 401);
                        }
                    }
                    else{
                        do{
                            $order_id = Str::random(10);
                            $order_id = strtoupper($order_id);
                            $checking_id = HotelDarmaBooking::where('agent_os_ref', $order_id)->get();
                        }
                        while(!$checking_id->isEmpty());
            
                        $idbooking = $order_id;

                        $bookingsession=HotelDarmaBookingSession::where('user_id',Auth::id())->update([
                            'room_id'=>$roomid,
                            'breakfast'=>$breakfast,
                            'cancel_policy'=>$bodyresponse->cancelPolicy,
                            'agent_os_ref' => $idbooking
                        ]);

                        $hotel = HotelDarma::where('id_darma', $hotelid)->first();

                        if(!$hotel['request_array'] || $hotel['request_array'] != $bodyresponse->specialRequestArrayRequired){
                            if($bodyresponse->specialRequestArrayRequired == true){
                                $hotelRequest = HotelDarma::where('id',$hotel['id'])->update([
                                    'request_array' => 1
                                ]);
                            }
                            else{
                                $hotelRequest = HotelDarma::where('id',$hotel['id'])->update([
                                    'request_array' => 0
                                ]);
                            }
                            
                        }

                        foreach((array)$bodyresponse->specialRequestArray as $key => $value){
                            $arrayRequest = [
                                "id" => $value->ID,
                                "hotel_id" => $hotel['id'],
                                "description" => $value->description
                            ];

                            $hotel_request = HotelDarmaRequestList::where('id', $value->ID)->where('hotel_id', $hotel['id'])->first();

                            if(!$hotel_request){
                                $newRequest = HotelDarmaRequestList::create($arrayRequest);
                            }
                        }
                        
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


    //MidTrans
    public function chargeMidtrans($transaction,$payment)
	{
		$username="SB-Mid-server-uUu-OOYw1hyxA9QH8wAbtDRl";
		$url="https://api.sandbox.midtrans.com/v2/charge";
		$data_array =  [
		    "payment_type"        => $payment->category->url,
		    "bank_transfer"       => [
		    	"bank"               => $payment->name
		    ],
            "custom_field1"        => "HotelDarma",
		    "transaction_details" => array(
		        "order_id"            => $transaction->agent_os_ref,
		        "gross_amount"		  => $transaction->total_price
		    ),
		];

		$header="Authorization: Basic ".base64_encode($username.":");
		// return json_encode($data_array)."BLABLABLAB".$header."davdavd".$username.":";
		$make_call = $this->callAPI($url, json_encode($data_array),$header);
		return $make_call;
	}

    public function cancelMidtrans($transaction, $payment)
    {
        //dd($transaction);

        $username="SB-Mid-server-uUu-OOYw1hyxA9QH8wAbtDRl";
        $url="https://api.sandbox.midtrans.com/v2/".$transaction->agent_os_ref."/cancel";
        $data_array =  array(
            "payment_type"          => $payment->category->url,
            "bank_transfer"         => array(
                "bank"              => $payment->name
            ),
            "custom_field1"        => "HotelDarma",
            "transaction_details"   => array(
                "order_id"          => $transaction->agent_os_ref,
                "gross_amount"      => $transaction->total_price
            ),
        );

        $header="Authorization: Basic ".base64_encode($username.":");
        // return json_encode($data_array)."BLABLABLAB".$header."davdavd".$username.":";
        $make_call = $this->callAPI($url, json_encode($data_array),$header);
        return $make_call;
    }

    //callAPI
    function callAPI( $url, $data, $header = false){
		$curl = curl_init();
      	curl_setopt($curl, CURLOPT_POST, 1);
      	if ($data)
      	   	curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

		// OPTIONS:
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		if(!$header){
	       	curl_setopt($curl, CURLOPT_HTTPHEADER, array(
	       	   	'Content-Type: application/json',
	       	));
	   	}else{
	   	    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
	   	       	'Content-Type: application/json',
	   	       	$header
	   	    ));
	   	}
		// EXECUTE:
		$result = curl_exec($curl);
		if(!$result){die("Connection Failure");}
		curl_close($curl);
		return $result;
	}

    //Search #3.5 - Make Local Booking

    public function createBooking(Request $request){
        $bookingsession=$this->checkSession(Auth::id());
        if(! $bookingsession){
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Search Hotel First!','data'=> '']), 401);
        }
        else{
            $validator = Validator::make($request->all(), [
                'email' => 'required',
                'phone' => 'required',
                'paxes' => 'required',
                'smoking_room' => 'required',
                'special_request' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->json(['error'=>$validator->errors()], 400);
            }
            else{
                $hotel = HotelDarma::where('id_darma', $bookingsession->hotel_id)->first();
                $room = HotelDarmaRoom::where('id_darma_room', $bookingsession->room_id)->first();

                $body = [
                    'hotel_id' => $hotel->id,
                    'room_id' => $room->id,
                    'user_id' => Auth::id(),
                    'reservation_no' => null,
                    'agent_os_ref' => $bookingsession->agent_os_ref,
                    'booking_date' => null,
                    'check_in' => $bookingsession->check_in_date,
                    'check_out' => $bookingsession->check_out_date,
                    'total_price' => $room->room_price,
                    'requests' => $request->special_request,
                    'breakfast' => $room->breakfast,
                    'status' => 'pending',
                    'cancelation_policy' => $bookingsession->cancel_policy
                ];

                $room_req_update = HotelDarmaBookingRoomReq::where('id_booking_session',$bookingsession->id)->update([
                    'smoking_room' => $request->smoking_room,
                    'phone' => $request->phone,
                    'email' => $request->email,
                    'request_description' => $request->special_request
                ]);

                $room_req = HotelDarmaBookingRoomReq::where('id_booking_session',$bookingsession->id)->first();
                $checkpaxes = HotelDarmaBookingPaxes::where('id_room_req', $room_req->id)->first();
                
                if(! $checkpaxes){
                    foreach($request->paxes as $key => $value){
                    

                        $newPaxesData = [
                            'id_room_req' => $room_req->id,
                            'title' => $value['title'],
                            'first_name' => $value['first_name'],
                            'last_name' => $value['last_name']
                        ];
                        
                        $newPaxes = HotelDarmaBookingPaxes::create($newPaxesData);
                    }
                }           

                $payment = PaymentMethod::where('id',$request->id_payment_method)->with('category')->first();
                $newbooking = HotelDarmaBooking::create($body);
                $newbooking['payment_data'] = json_decode($this->chargeMidtrans($newbooking, $payment));

                if($newbooking){
                    $newbooking_data = HotelDarmaBooking::where('id',$newbooking->id)->first();
                    
                    $data['payment_type'] = $newbooking->payment_data->payment_type;
                    $data['amount']=$newbooking->payment_data->gross_amount;
                    $data['payment_status']=$newbooking->payment_data->transaction_status;
                    foreach ($newbooking->payment_data->va_numbers as $key => $value) {
                        $data['virtual_account']=$value->va_number;
                        $data['bank']=$value->bank;
                    }

                    $newbooking_data['payment'] = $data;
                }

                $hotel_payment = HotelDarmaPayment::create([
                    'booking_id' => $newbooking_data->id,
                    'payment_method_id' => $request->id_payment_method,
                    'midtrans_id' => '',
                    'va_number' => $newbooking_data->payment['virtual_account'],
                    'settlement_time' => null,
                    'payment_status' => 'pending'
                ]);

                return response()->json(new ValueMessage(['value'=>1,'message'=>'Create Booking Success!','data'=>  $newbooking_data, $hotel_payment]), 200);
            }
            
        }
    }

    public function issueBooking($user_id){
        $bookingsession=$this->checkSession($user_id);
        if(! $bookingsession){
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Search Hotel First!','data'=> '']), 401);
        }
        else{
            $userid=$this->username;
            $token=$this->checkLoginUser($user_id);
            $passport = $bookingsession->pax_passport;
            $country = $bookingsession->country_id;
            $city = $bookingsession->city_id;
            $checkin = $bookingsession->check_in_date;
            $checkout = $bookingsession->check_out_date;
            $hotelid = $bookingsession->hotel_id;
            $roomid = $bookingsession->room_id;
            $internalcode = $bookingsession->internal_code;
            $breakfast = $bookingsession->breakfast;

            $room_req_data = HotelDarmaBookingRoomReq::where('id_booking_session',$bookingsession->id)->first();

            $paxes_array = [];
            $getpaxes = HotelDarmaBookingPaxes::where('id_room_req', $room_req_data->id)->get();

            foreach($getpaxes as $key => $value){

                $pax = [
                    'title' => $value->title,
                    'firstName' => $value->first_name,
                    'lastName' => $value->last_name
                ];
                
                array_push($paxes_array, $pax);
            }

            $booking_data = HotelDarmaBooking::where('agent_os_ref', $bookingsession->agent_os_ref)->first();

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

            $hotel = HotelDarma::where('id_darma', $bookingsession->hotel_id)->first();

            if($hotel['request_array'] == true){
                $request_id = explode(',', $room_req_data['request_description']);
                $special_request = [];

                foreach($request_id as $key => $value){

                    $getDesc = HotelDarmaRequestList::where('id', $value)->where('hotel_id', $hotel['id'])->first();

                    $new_request = (object) [
                        "ID" => $getDesc['id'],
                        "description" => $getDesc['description']
                    ];

                    array_push($special_request, $new_request);

                }

                $room_request = [
                    'roomType' => $roomtype,
                    'isRequestChildBed' => $room_req_data['is_request_child_bed'],
                    'childNum' => $room_req_data['child_num'],
                    'childAges' => explode(',', $room_req_data['child_age']),
                    'paxes' => $paxes_array,
                    'isSmokingRoom' => $room_req_data['smoking_room'],
                    'phone' => $room_req_data['phone'],
                    'email' => $room_req_data['email'],
                    'specialRequestArray' => $special_request,
                    'requestDescription' => "None"
                ];
            }
            else{
                $room_request = [
                    'roomType' => $roomtype,
                    'isRequestChildBed' => $room_req_data['is_request_child_bed'],
                    'childNum' => $room_req_data['child_num'],
                    'childAges' => explode(',', $room_req_data['child_age']),
                    'paxes' => $paxes_array,
                    'isSmokingRoom' => $room_req_data['smoking_room'],
                    'phone' => $room_req_data['phone'],
                    'email' => $room_req_data['email'],
                    'requestDescription' => $room_req_data['request_description']
                ];
            }

            $bedType = [
                'ID' => 0,
                'bed' => "FullBed"
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
                    'roomRequest' => array($room_request),
                    'bedType' => $bedType,
                    'agentOsRef' => $bookingsession->agent_os_ref
                ];

                $response=$this->client->request(
                    'POST',
                    'Hotel/BookingAllSupplier',
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
                
                if($bodyresponse->status=="FAILED" && $bodyresponse->respMessage != "ticket status is PROCESSED please check booking detail"){
                    if($bodyresponse->respMessage=="member authentication failed"){
                        return response()->json(new ValueMessage(['value'=>0,'message'=>'Access Token Wrong!','data'=> '']), 401);
                    }else if($bodyresponse->respMessage=="wrong format request or null mandatory data"){
                        return response()->json(new ValueMessage(['value'=>0,'message'=>'Data is incomplete!','data'=> '']), 403);
                    }else if($bodyresponse->respMessage=="search hotel expired"){
                        return response()->json(new ValueMessage(['value'=>0,'message'=>'Search Data Expired!','data'=> '']), 401);
                    }else if($bodyresponse->respMessage=="booking hotel failed"){
                        return response()->json(new ValueMessage(['value'=>0,'message'=>'Booking Failed!','data'=> '']), 401);
                    }
                }
                else{
                    $booking_issue = HotelDarmaBooking::where('agent_os_ref',$bodyresponse->agentOsRef)->update([
                        'reservation_no' => $bodyresponse->reservationNo,
                        'booking_date' => $bodyresponse->bookingDate,
                        'os_ref_no' => $bodyresponse->osRefNo
                    ]);

                    $bookingid = HotelDarmaBooking::where('agent_os_ref',$bodyresponse->agentOsRef)->first();


                    $check_dup_paxes = HotelDarmaPaxesList::where('booking_id', $bookingid['id'])->first();
                    
                    //jump
                    if($check_dup_paxes == null){
                        foreach($getpaxes as $key => $value){
                            $booking_paxes_data = [
                                'booking_id' => $bookingid['id'],
                                'title' => $value->title,
                                'first_name' => $value->first_name,
                                'last_name' => $value->last_name
                            ];

                            $booking_paxes = HotelDarmaPaxesList::create($booking_paxes_data);
                        }
                        
                    }
                        
                
                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Success!','data'=> $bodyresponse]), 200);
                }

            }
            catch(RequestException $e) {
                if ($e->hasResponse()) {
                    echo Psr7\Message::toString($e->getResponse());
                }
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Access Token Wrong!','data'=> '']), 401);
            }
            return response()->json(new ValueMessage(['value'=>0,'message'=>'not get!','data'=> '']), 401);
        }

    }

    public function getBookingList(Request $request){
        $booking = HotelDarmaBooking::where('user_id', Auth::id())->get();

        if($booking){
            $user_id = Auth::id();

            $paid = ['success', 'process'];
            $cancel = ['cancel', 'expire'];

            //check and update status process
            $processtrans = HotelDarmaBooking::where('user_id', $user_id)->with('hotel', 'payment', 'room')->where('status', 'process')->orderBy('updated_at', 'DESC')->get();

            foreach($processtrans as $keypro => $valuepro){
                $request = new Request();
                $request->agent_os_ref = $valuepro->agent_os_ref;
                $check_status = $this->getBookingDetail($request);
                //dd($check_status);
                //ErrorException: Trying to get property 'voucherNo' of non-object

                if(isset($check_status->voucherNo)){
                    //down
                    $booking_session = HotelDarmaBookingSession::where('agent_os_ref', $valuepro->agent_os_ref)->first();
                    $room_req = HotelDarmaBookingRoomReq::where('id_booking_session',$booking_session['id'])->first();
                    $getpaxes = HotelDarmaBookingPaxes::where('id_room_req', $room_req['id'])->get();

                    $check_dup_paxes = HotelDarmaPaxesList::where('booking_id', $valuepro->id)->first();

                    //dd($check_dup_paxes);
                    
                    if($check_dup_paxes == null){
                        foreach($getpaxes as $key => $value){
                            $booking_paxes_data = [
                                'booking_id' => $valuepro->id,
                                'title' => $value->title,
                                'first_name' => $value->first_name,
                                'last_name' => $value->last_name
                            ];

                            $booking_paxes = HotelDarmaPaxesList::create($booking_paxes_data);
                        }
                        
                    }

                    $update_booking = HotelDarmaBooking::where('id', $valuepro->id)->update([
                        'status' => 'success',
                        'reservation_no' => $check_status->voucherNo
                    ]);
                    
                    

                    $token = [];
                    $usertoken = PersonalAccessToken::select('name')->where('tokenable_id', $valuepro->user_id)->get();

                    $hotel_name = HotelDarma::select('hotel_name')->where('id', $valuepro->hotel_id)->first();

                    foreach($usertoken as $key => $value){
                        array_push($token, $value->name); 
                    }

                    foreach ($token as $key => $value) {

                        NotificationController::sendPush($valuepro->user_id,$value, "Booking issued", "Your reservation for booking at".$hotel_name['hotel_name']." is issued", "Hotel", "finish");
                    }
                }
            }

            
            //masukan all booking ke  list
            $paidtrans = HotelDarmaBooking::where('user_id', $user_id)->with('hotel', 'payment', 'room')->whereIn('status', $paid)->orderBy('updated_at', 'DESC')->get();
            $unpaidtrans = HotelDarmaBooking::where('user_id', $user_id)->with('hotel', 'payment', 'room')->where('status', 'pending')->orderBy('updated_at', 'DESC')->get();
            $canceltrans = HotelDarmaBooking::where('user_id', $user_id)->with('hotel', 'payment', 'room')->whereIn('status', $cancel)->orderBy('updated_at', 'DESC')->get();

            foreach($paidtrans as $key => $value){
                $total_guest = HotelDarmaPaxesList::where('booking_id', $value->id)->count();
                $value->total_guests = $total_guest;

                $total_night = strtotime($value->check_out) - strtotime($value->check_in);
                $value->total_nights = $total_night / 86400;

                $payment_method = HotelDarmaPayment::where('booking_id', $value->id)->first();
                $payment = PaymentMethod::where('id',$payment_method['payment_method_id'])->with('category')->first();
                $value->payment_method = $payment;

                $images = HotelDarmaImage::where('hotel_id', $value->hotel_id)->get();
                $value->images = $images;

                $hotel = HotelDarma::where('id', $value->hotel_id)->first();
                $special_request = [];
                
                if($hotel['request_array'] == true){
                    $request_id = explode(',', $value->requests);
                    

                    foreach($request_id as $key_req => $value_req){

                        $getDesc = HotelDarmaRequestList::where('id', $value_req)->where('hotel_id', $hotel['id'])->first();

                        $new_request = (object) [
                            "ID" => $getDesc['id'] ?? '0',
                            "description" => $getDesc['description'] ?? $value_req
                        ];
                        array_push($special_request, $new_request);
                    }
                    
                    //dd($special_request);
                    $value->special_request = $special_request;
                }
                else{
                    $value->special_request = $value->requests;
                }
                

                //$value->special_request = "obj";
            }
            foreach($unpaidtrans as $key => $value){
                $total_guest = HotelDarmaPaxesList::where('booking_id', $value->id)->count();
                $value->total_guests = $total_guest;

                $total_night = strtotime($value->check_out) - strtotime($value->check_in);
                $value->total_nights = $total_night / 86400;

                $payment_method = HotelDarmaPayment::where('booking_id', $value->id)->first();
                $payment = PaymentMethod::where('id',$payment_method['payment_method_id'])->with('category')->first();
                $value->payment_method = $payment;

                $images = HotelDarmaImage::where('hotel_id', $value->hotel_id)->get();
                $value->images = $images;

                $hotel = HotelDarma::where('id', $value->hotel_id)->first();
                $special_request = [];
                
                if($hotel['request_array'] == true){
                    $request_id = explode(',', $value->requests);
                    

                    foreach($request_id as $key_req => $value_req){

                        $getDesc = HotelDarmaRequestList::where('id', $value_req)->where('hotel_id', $hotel['id'])->first();

                        $new_request = (object) [
                            "ID" => $getDesc['id'] ?? '0',
                            "description" => $getDesc['description'] ?? $value_req
                        ];
                        array_push($special_request, $new_request);
                    }
                    
                    //dd($special_request);
                    $value->special_request = $special_request;
                }
                else{
                    $value->special_request = $value->requests;
                }
                
            }
            foreach($canceltrans as $key => $value){

                $total_night = strtotime($value->check_out) - strtotime($value->check_in);
                $value->total_nights = $total_night / 86400;
            }

            $data['paid'] = $paidtrans->values();
            $data['unpaid'] = $unpaidtrans->values();
            $data['cancelled'] = $canceltrans->values();

            return response()->json(new ValueMessage(['value'=>1, 'message'=>'Get Data Success!', 'data'=> $data]), 200); 

        }
        else{
            return response()->json(new ValueMessage(['value'=>0, 'message'=>'Data Not Found!', 'data'=> '']), 404);
        }

    }

    public function cancel(Request $request){
        $booking = HotelDarmaBooking::where('id', $request->booking_id)->first();

        if($booking){
            if($booking['user_id'] != Auth::id()){
                return response()->json(new ValueMessage(['value'=>0, 'message'=>'Unauthorized!', 'data'=> '']), 401);
            }
            else{
                $payment_data = HotelDarmaPayment::where('booking_id', $booking['id'])->first();
                $payment = PaymentMethod::where('id',$payment_data['payment_method_id'])->with('category')->first();
                $cancel = json_decode($this->cancelMidtrans($booking, $payment));

                return response()->json(new ValueMessage(['value'=>1,'message'=>'Transaction cancelled!','data'=> $cancel]), 200);
            }
            
        }
        else{
            return response()->json(new ValueMessage(['value'=>0, 'message'=>'Booking Data Not Found!', 'data'=> '']), 404);
        }
    }

    /*
    public function getBookingList(Request $request){
        $userid=$this->username;
        $token=$this->checkLoginUser();

        $body = [
            'userID'=>$userid,
            'accessToken'=>$token,
            'filterDate' => 'Booking',
            'dateStart' => $request->date_start,
            'dateEnd' => $request->date_end
        ];


        try {
            $response=$this->client->request(
                'POST',
                'Hotel/BookingList',
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
                return response()->json(new ValueMessage(['value'=>1,'message'=>'Get Booking List Success!','data'=> $bodyresponse->hotelBookingList]), 200);
            }
        }catch(RequestException $e) {
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Access Token Wrong!','data'=> '']), 401);
        }
        return response()->json(new ValueMessage(['value'=>0,'message'=>'not get!','data'=> '']), 401);
    }
    */

    public function getBookingDetail(Request $request){
        $userid=$this->username;
        $token=$this->checkLoginUser(Auth::id());

        if($request->reservation_no){
            $booking = HotelDarmaBooking::where('reservation_no', $request->reservation_no)->first();

            $body = [
                'userID'=>$userid,
                'accessToken'=>$token,
                'osRefNo' => $booking['os_ref_no'],
                'reservationNo' => $request->reservation_no,
                'agentOsRef' => $booking['agent_os_ref']
            ];
        }
        else{
            $booking = HotelDarmaBooking::where('agent_os_ref', $request->agent_os_ref)->first();

            $body = [
                'userID'=>$userid,
                'accessToken'=>$token,
                'osRefNo' => $booking['os_ref_no'],
                'reservationNo' => $booking['reservation_no'],
                'agentOsRef' => $request->agent_os_ref
            ];
        }

        

        try {
            $response=$this->client->request(
                'POST',
                'Hotel/BookingDetail',
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
                    return $bodyresponse->respMessage;
                    //return response()->json(new ValueMessage(['value'=>0,'message'=>'Access Token Wrong!','data'=> '']), 401);
                }
            }
            else{
                return $bodyresponse->bookingDetail;
                //return response()->json(new ValueMessage(['value'=>1,'message'=>'Get Booking Detail Success!','data'=> $bodyresponse->bookingDetail]), 200);
            }
        }catch(RequestException $e) {
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Access Token Wrong!','data'=> '']), 401);
        }
        return response()->json(new ValueMessage(['value'=>0,'message'=>'not get!','data'=> '']), 401);        
    }

    public function testImage(Request $request){
        $image = HotelDarmaImage::where('hotel_id', $request->hotel_id)->first();

        if(!$image){
            return response()->json(new ValueMessage(['value'=>0,'message'=>'No Images!','data'=> '']), 404);
        }
        else{
            $image_id = substr($image['image'], strpos($image['image'],"=") + 1);

            $hotel = HotelDarma::where('id', $request->hotel_id)->first();

    
            //return file_get_contents("https://61.8.74.42:7080/H2H/hotel/Image?ID=3994828-1");


            //[
                //'sink' => $temp;
              //  'sink' => storage_path('hotel/'.str_replace(' ','-', 'hotel_'.$hotel['hotel_name'].'_'.substr($image_id, -1)).'.jpeg')
            //]

            
            try {
                $filename = str_replace(' ','-', 'hotel_'.$hotel['hotel_name'].'_'.substr($image_id, -1)).'.jpeg';
                $response=$this->client->request(
                    'GET',
                    'hotel/Image?ID='.$image_id,
                    [
                        //'sink' => $temp
                        'sink' => storage_path('app/public/hotel/'.$filename)
                    ]
                );

                //$file = $response->getBody()->getContents();
                //$filename = str_replace(' ','-', 'hotel_'.$hotel['hotel_name'].'_'.substr($image_id, -1)).'.jpeg';
                //$temp->move('hotel', $filename);

                $update_path = HotelDarmaImage::where('id', $image['id'])->update([
                    'path' => 'http://hainaservice.com/storage/hotel/'.$filename
                ]);

                return response()->json(new ValueMessage(['value'=>1,'message'=>'Images stored!','data'=> '']), 200);
            }
            catch(RequestException $e) {
                if ($e->hasResponse()) {
                    $response = $e->getResponse();
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Storing Error!','data'=> $response->getStatusCode()." ".$response->getReasonPhrase()." ".$response->getBody()]), 401);
                } 
            }
            

            
        }
    }

    public function searchHotelQuery(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search' => 'required',
            'check_in_date' => 'required',
            'check_out_date' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }
        else{
            
            $this->deleteSession(Auth::id());

            $userid = $this->username;
            $token = $this->checkLoginUser(Auth::id());
            $room_request = [
                'roomType' => "Single",
                'isRequestChildBed' => false,
                'childNum' => 0,
                'childAges' => []
            ];


            try{
                $body = [
                    'hotelNameFilter' => $request->search,
                    'userID' => $userid,
                    'accessToken' => $token,
                    'checkInDate' => $request->check_in_date,
                    'checkOutDate' => $request->check_out_date,
                    'roomRequest' => array($room_request)
                ];

                $response=$this->client->request(
                    'POST',
                    'Hotel/HotelList5',
                    [
                        'form_params' => $body,
                        'on_stats' => function (TransferStats $stats) use (&$url) {
                            $url = $stats->getEffectiveUri();
                        }
                    ]  
                );

                $bodyresponsehotel=json_decode($response->getBody()->getContents());
        
                DarmawisataRequest::insert(
                    [
                        'request'=>json_encode($body),
                        'response'=>json_encode($bodyresponsehotel),
                        'status'=>$bodyresponsehotel->status,
                        'url'=>$url,
                        'response_code'=>$response->getStatusCode()
                    ]
                );

                $body = [
                    'userID'=>$userid,
                    'accessToken'=>$token,
                    'countryID'=>"ID",
                    'cityNameFilter'=>$request->search
                ];
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

                $bodyresponsecity=json_decode($response->getBody()->getContents());

                DarmawisataRequest::insert(
                    [
                        'request'=>json_encode($body),
                        'response'=>json_encode($bodyresponsecity),
                        'status'=>$bodyresponsecity->status,
                        'url'=>$url,
                        'response_code'=>$response->getStatusCode()
                    ]
                );

                if($bodyresponsehotel->status=="FAILED"||$bodyresponsecity->status=="FAILED"){
                    if($bodyresponsehotel->respMessage=="member authentication failed"||$bodyresponsecity->respMessage=="member authentication failed"){
                        return response()->json(new ValueMessage(['value'=>0,'message'=>'Access Token Wrong!','data'=> '']), 401);
                    }else if($bodyresponsecity->respMessage=="wrong format request or null mandatory data"||$bodyresponsehotel->respMessage=="wrong format request or null mandatory data"){
                        return response()->json(new ValueMessage(['value'=>0,'message'=>'Data is incomplete!','data'=> '']), 403);
                    }else if($bodyresponsehotel->respMessage=="hotel not available"){
                        return response()->json(new ValueMessage(['value'=>0,'message'=>'Hotel Unavailable!','data'=> '']), 401);
                    }else if($bodyresponsehotel->respMessage=="no room found"){
                        return response()->json(new ValueMessage(['value'=>0,'message'=>'No Room Found!','data'=> '']), 401);
                    }
                }
                else{
                    $data=[];
                    foreach ($bodyresponsecity->cities as $key => $value) {
                        $value->Type="city";
                        $value->City="Indonesia";
                        $value->id_city=$value->ID;
                        unset($value->CountryID);
                        array_push($data,$value);
                    }
                    foreach ($bodyresponsehotel->hotels as $key => $value) {
                        $city=City::where('id_darma',$value->cityID)->first();
                        if($city){
                            $value->City=$city->name;
                            $value->id_city=$value->cityID;
                            $value->Type="hotels";
                            unset($value->countryID);
                            unset($value->cityID);
                            array_push($data,$value);
                        }
                    }

                    
                    $body = [
                        'user_id' => Auth::id(),
                        'pax_passport' => "ID",
                        'country_id' => "ID",
                        'check_in_date' => $request->check_in_date,
                        'check_out_date' => $request->check_out_date
                    ];
    
                    $booksession = HotelDarmaBookingSession::create($body);

                    $roomreq_session = [
                        'id_booking_session' => $booksession->id,
                        'room_type' => 0,
                        'is_request_child_bed' => 0,
                        'child_num' => 0,
                    ];

                    $roomrequestdata = HotelDarmaBookingRoomReq::create($roomreq_session);




                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Success!','data'=> $data]), 200);

                }
            }catch(RequestException $e) {
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Access Token Wrong!','data'=> '']), 401);
            }
        }
    }

    public function bookingData(Request $request){
        $validator = Validator::make($request->all(), [
            'booking_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 400);
        }
        else{
            $booking = HotelDarmaBooking::where('id', $request->booking_id)->with('payment', 'hotel', 'room', 'users', 'paxes', 'request')->first();

            if($booking){

                if($booking['user_id'] != Auth::id()){
                    return response()->json(new ValueMessage(['value'=>0,'message'=>'Unauthorized!','data'=> '']), 401);
                }
                else{
                    unset($booking->users->firebase_uid);
                    unset($booking->users->expected_salary);
                    return response()->json(new ValueMessage(['value'=>1,'message'=>'Get Booking Data Successful!','data'=> $booking]), 401);
                }

            }
            else{
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Booking Data Not found!','data'=> '']), 404);
            }
        }
    }

}
