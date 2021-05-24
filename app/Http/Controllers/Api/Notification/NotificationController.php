<?php

namespace App\Http\Controllers\Api\Notification;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ValueMessage;
use Illuminate\Support\Facades\Validator;

use App\Models\UserNotification;
use App\Models\User;
use App\Models\NotificationCategory;

class NotificationController extends Controller
{

    protected $serverKey;
    
	public function __construct()
    {
        $this->serverKey = 'AAAA8gxroJU:APA91bEVVjGrc-JmrOVW20ntmKdCjfq603SF976B6b5mIiZqRm97ahljd-5d58lhza9jBz860aKChLrPou8eGzpe0ttLkgJujd4_iWbaaYb3rwzh_zBtw2uCssTDwXqJwKQItyaZrebn';
    }

	public function sendPush ($token, $title, $body)
    {
        $headers = [
            'Authorization: key=' . $this->serverKey,
            'Content-Type: application/json',
        ];

        $data = [
            "to" => $token,
            "notification" =>
                [
                    "title" => $title,
                    "body" => $body
                ],
        ];

        $dataString = json_encode($data);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);

        return curl_exec($ch);
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

    public function notifSend(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'title' => 'required',
            'body' => 'required',
        ]);

        if ($validator->fails()) {          
            return response()->json(['error'=>$validator->errors()], 400);                        
        }else{
            $retdata='';

            foreach ($request->token as $key => $value) {
                $retdata=$retdata.$this->sendPush($value,$request->title,$request->body);
            }
            return $retdata;

        }    
    }




    public function getUserNotification(Request $request)
    {
        $usernotif=UserNotification::where('id_user',$request->user()->id)->with('notificationcategory')->get();


        if($usernotif->isEmpty()){
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Notification Doesn\'t Exist!','data'=> '']), 404);
        }else{

            return response()->json(new ValueMessage(['value'=>1,'message'=>'Get User Notification Success!','data'=> $usernotif]), 200);
        }
    }


}
