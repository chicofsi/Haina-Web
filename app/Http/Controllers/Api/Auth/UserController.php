<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\User as UserResource;
use App\Http\Resources\ValueMessage;
use Illuminate\Support\Facades\URL;
use Firebase\Auth\Token\Exception\InvalidToken;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\UserLogs;
use App\Models\UserGoogle;

class UserController extends Controller
{
    
    public $successStatus = 200;

    public function check(Request $request)
    {
        $token=$request->header('Authorization');

        $tokens = DB::table('personal_access_tokens')->get();

        foreach ($tokens as $key => $val) {
            if(Str::contains($token, $val->id)){
                $success =  $val->tokenable_id;
                $user=User::where('id',$success)->first();

                return $user;
            }
        }
        return "fail";

    }

    public function login(Request $request)
    {
        
    	$validator = Validator::make($request->all(), [
            'device_name' => 'required',
            'device_token' => 'required',
            'email' => 'email|required',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Field Empty!','data'=> $validator->errors()]), 400);
        }
        else{

            if(auth()->guard('web-users')->attempt($request->only('email','password'))){

                $user = auth()->guard('web-users')->user();
                $success=  $user->createToken($request->device_token)->plainTextToken;

                UserLogs::create([
                   'id_user' => $user->id,
                   'id_user_activity' => 1,
                   'message' => 'User successfully login on '.$request->device_name.' with ip '.$request->ip()
                ]);




                return response()->json(new ValueMessage(['value'=>1,'message'=>'Login Success!','data'=> $success]), $this->successStatus);

            }
            else{
                return response()->json(new ValueMessage(['value'=>0,'message'=>'User Credential Wrong!','data'=> '']), 401);
            }     
           
        }
        
    
        
	    
    }

    public function loginWithGoogle(Request $request)
    {
        // Launch Firebase Auth

        $auth = app('firebase.auth');
        // Retrieve the Firebase credential's token
        $idTokenString = $request->firebase_token;

        
        try { // Try to verify the Firebase credential token with Google
          
            $verifiedIdToken = $auth->verifyIdToken($idTokenString);
          
        } catch (\InvalidArgumentException $e) { // If the token has the wrong format
          
            return response()->json([
                'message' => 'Unauthorized - Can\'t parse the token: ' . $e->getMessage()
            ], 401);        
          
        } catch (InvalidToken $e) { // If the token is invalid (expired ...)
          
            return response()->json([
                'message' => 'Unauthorized - Token is invalide: ' . $e->getMessage()
            ], 401);
          
        }

        // Retrieve the UID (User ID) from the verified Firebase credential's token
        $uid = $verifiedIdToken->claims()->get('sub');

        $data = $auth->getUser($uid);

        $user=User::where('email',$data->email)->first();
        if($user){
            User::where('email',$data->email)->update([
                'firebase_uid'=>$data->uid
            ]);
            $success = $user->createToken($request->device_token)->plainTextToken;

            return response()->json(new ValueMessage(['value'=>1,'message'=>'Login Success!','data'=> $success]), $this->successStatus);
        }else{
            $user=UserGoogle::updateOrCreate([
                'uid' => $data->uid,
            ],
            [
                'display_name' => $data->displayName,
                'email' => $data->email,
            ]);
            return response()->json(new ValueMessage(['value'=>1,'message'=>'Please Continue Registration!','data'=> ""]), $this->successStatus);


        }
        
        
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fullname' => 'required',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|unique:users,phone',
            'username' => 'required|unique:users,username',
            'password' => 'required',
            'device_token' => 'required',
            'device_name' => 'required',
            
        ]);

        if ($validator->fails()) {
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Field Empty!','data'=> $validator->errors()]), 400);         
        }else{
            $input = $request->all();

            $usergoogle=UserGoogle::where('email',$request->email)->first();
            if($usergoogle){
                $input['firebase_uid']=$usergoogle->uid;
                UserGoogle::where('email',$request->email)->delete();
            }
            
            $input['password'] = Hash::make($request['password']);
            $user = User::create($input);
            $success['token'] =  $user->createToken($request->device_token)->plainTextToken;

            UserLogs::create([
                   'id_user' => $user->id,
                   'id_user_activity' => 2,
                   'message' => 'User successfully registered to the sistem on '.$request->device_name.', username='.$user->username
                ]);

            return response()->json(new ValueMessage(['value'=>1,'message'=>'Register Success!','data'=> $success]),$this->successStatus);
        }

        

    }

    public function logout(Request $request)
    {
       	
        UserLogs::create([
                   'id_user' => $request->user()->id,
                   'id_user_activity' => 3,
                   'message' => null
                ]);

        $request->user()->currentAccessToken()->delete();
        return response()->json(new ValueMessage(['value'=>1,'message'=>'Logout Success','data'=> '']),$this->successStatus);
    }

    public function detail(Request $request)
    {
        if($request->user()->photo==null){
            $data=$request->user(); 
            $data->photo='user_photo/default_user.jpg';
        }else{
            $data=$request->user();
        }
   		return response()->json(new ValueMessage(['value'=>1,'message'=>'Get Data Success','data'=> new UserResource($data)]),$this->successStatus);
	
    }
    public function updatePhoto(Request $request)
    {
        if ($files = $request->file('photo')) {
        
            $fileName= str_replace(' ','-',$request->user()->id."_".$request->user()->username);
            $guessExtension = $request->file('photo')->guessExtension();

            //store file into document folder
            $file = $request->photo->storeAs('public/user_photo',$fileName.'.'.$guessExtension);

            User::where('id',$request->user()->id)->update(['photo'=>substr($file,7)]);

            UserLogs::create([
                   'id_user' => $request->user()->id,
                   'id_user_activity' => 6,
                   'message' => null
                ]);


            return response()->json(new ValueMessage(['value'=>1,'message'=>'Update Photo Success','data'=> '']),$this->successStatus);
        }
        
    }

    public function updateProfile(Request $request)
    {

        $change=[];
        if($request->has('fullname')){
            $change['fullname']=$request->fullname;
        }
        if($request->has('address')){
            $change['address']=$request->address;
        }
        if($request->has('birthdate')){
            $change['birthdate']=$request->birthdate;
        }
        if($request->has('gender')){
            $change['gender']=$request->gender;
        }
        if($request->has('about')){
            $change['about']=$request->about;
        }
        

        User::where('id',$request->user()->id)->update($change);


        $text="";
        foreach ($change as $key => $value) {
            $text=$text.$key." to '".$value."', ";
        }

        UserLogs::create([
                   'id_user' => $request->user()->id,
                   'id_user_activity' => 6,
                   'message' => "User succesfully update the profile. ".$text
                ]);


        return response()->json(new ValueMessage(['value'=>1,'message'=>'Update Profile Success','data'=> '']),$this->successStatus);
  
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(new ValueMessage(['value'=>0,'message'=>'Field Empty!','data'=> $validator->errors()]), 400);         
        }else{
            if(auth()->guard('web-users')->attempt(['email'=>$request->user()->email,'password'=>$request->current_password])){

                User::where('id',$request->user()->id)->update([
                    'password'=>Hash::make($request->new_password)
                ]);

                UserLogs::create([
                   'id_user' => $request->user()->id,
                   'id_user_activity' => 5,
                   'message' => null
                ]);


                return response()->json(new ValueMessage(['value'=>1,'message'=>'Change Password Success','data'=> '']),$this->successStatus);
            }else{
                return response()->json(new ValueMessage(['value'=>0,'message'=>'Password Wrong','data'=> '']),401);
            }
            
        }
    
    }
}
