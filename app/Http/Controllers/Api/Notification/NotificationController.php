<?php

namespace App\Http\Controllers\Api\Notification;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ValueMessage;
use Illuminate\Support\Facades\Validator;

use App\Models\UserNotification;
use App\Models\User;
use App\Models\NotificationCategory;
use App\Models\NotificationOut;

class NotificationController extends Controller
{
    /*
    public function sendMessage(Request $request) {
        $content      = array(
            "en" => $request->message
        );
        $hashes_array = array();
        array_push($hashes_array, array(
            "id" => "like-button",
            "text" => "Like",
            "icon" => "http://i.imgur.com/N8SN8ZS.png",
            "url" => "https://hainaservice.com"
        ));
        array_push($hashes_array, array(
            "id" => "ok-button",
            "text" => "Go",
            "icon" => "http://i.imgur.com/N8SN8ZS.png",
            "url" => "https://hainaservice.com"
        ));
        $fields = array(
            'app_id' => "cb3a2a52-1950-4d94-9b7a-c06d1c47c56a",
            'included_segments' => array(
                $request->segment
            ),
            'data' => array(
                "foo" => "bar"
            ),
            'contents' => $content,
            'web_buttons' => $hashes_array
        );
        
        $fields = json_encode($fields);
        print("\nJSON sent:\n");
        print($fields);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
            'Authorization: Basic NjM1NTljMWUtMWRhYi00MWJhLWE4NjQtODkzMzJjYjdjMzZl'
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return $response;
    }
    */


    

	public static function sendPush ($id_user, $token, $title, $body, $type,$tabs)
    {
        $serverKey = 'AAAA8gxroJU:APA91bEVVjGrc-JmrOVW20ntmKdCjfq603SF976B6b5mIiZqRm97ahljd-5d58lhza9jBz860aKChLrPou8eGzpe0ttLkgJujd4_iWbaaYb3rwzh_zBtw2uCssTDwXqJwKQItyaZrebn';
        $headers = [
            'Authorization: key=' . $serverKey,
            'Content-Type: application/json',
        ];

        $data = [
            "to" => $token,
            "notification" =>
                [
                    "title" => $title,
                    "body" => $body
                ],
            "data" => [
                    "page" => $type,
                    "tabs" => $tabs
                ]
        ];

        $dataString = json_encode($data);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

        NotificationOut::create([
            'id_user' => $id_user,
            'firebase_token' => $token,
            "title" => $title,
            "body" => $body,
            "type" => $type,
            "tabs" => $tabs
        ]);

        return curl_exec($ch);
    }


    public function notifSend(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'title' => 'required',
            'body' => 'required',
            'page' => 'required',
        ]);

        if ($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 400);                        
        }else{
            $retdata='';

            foreach ($request->token as $key => $value) {
                $retdata=$retdata.$this->sendPush($value,$request->title,$request->body,$request->page,$request->tabs);
            }
            return $retdata;

        }    
    }


    public function createNotif($idUser,$message,$idCategory)
    {
        $notification = UserNotification::create([
            'message' => $message,
            'id_category' => $idCategory,
            'id_user' => $idUser,
        ]);

        $notifcat = NotificationCategory::where('id',$idCategory)->first();

        $user=User::where('id',$idUser)->first();

        $token=$user->tokens()->get()->pluck('name');
        $title=$notifcat->name;
        $body=$message;

        foreach ($token as $key => $value) {
            $this->sendPush($value,$title,$body);
        }

        

    }


    public function getUserNotification(Request $request)
    {
        $usernotif=UserNotification::where('id_user',$request->user()->id)->with('notificationcategory')->orderBy('created_at', 'desc')->get();


        if($usernotif->isEmpty()){
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Notification Doesn\'t Exist!','data'=> '']), 404);
        }else{

            return response()->json(new ValueMessage(['value'=>1,'message'=>'Get User Notification Success!','data'=> $usernotif]), 200);
        }
    }

    


}
