<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Password;
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
use App\Models\EmailTokenModels;

// for email configuration
use App\Mail\VerifyEmail;
use Illuminate\Support\Facades\Mail;

// use Laravel's built-in function
use Carbon\Carbon;

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

            // regex for password
            $uppercase = preg_match('@[A-Z]@', $request->password);
            $lowercase = preg_match('@[a-z]@', $request->password);
            $number = preg_match('@[0-9]@', $request->password);
            if (!$uppercase || !$lowercase || !$number || strlen($request->password) < 8)
            return response()->json([
              'error' => [
                  'code' => 400,
                  'detail' => 'bad_request',
                  'message' => 'password must contain lowercase letters, uppercase letters, numbers and at least 8 characters',
              ]
            ]);

            $input = $request->all();

            $usergoogle=UserGoogle::where('email',$request->email)->first();
            if($usergoogle){
                $input['firebase_uid']=$usergoogle->uid;
                UserGoogle::where('email',$request->email)->delete();
            }

            $input['password'] = Hash::make($request['password']);
            $user = User::create($input);

            # $user->sendEmailVerificationNotification();

            //event(new Registered($user));
            $fix_token = $user->createToken($request->device_token)->plainTextToken;
            $success['token'] = $fix_token;

            UserLogs::create([
                   'id_user' => $user->id,
                   'id_user_activity' => 2,
                   'message' => 'User successfully registered to the sistem on '.$request->device_name.', username='.$user->username
                ]);

            //return response()->json(new ValueMessage(['value'=>1,'message'=>'Register Success!','data'=> $success]),$this->successStatus);
            try {
              // save to email_tokens
              // create ids
              $ids = $this->generate_random_string(64);
              // current time
              $now = Carbon::now();
              // set valid_until
              $valid_until = date('Y-m-d H:i:s', strtotime('+1 hours', strtotime($now)));
              // save
              $create = EmailTokenModels::create([
                'ids' => $ids,
                'email' => $request->email,
                'token' => $fix_token,
                'valid_until' => $valid_until,
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null
              ]);
              $data = [
                'email_destination' => $request->email,
                'fix_token' => $fix_token,
                'url_activation' => url('/email-verified?user='.$request->email.'&token='.$fix_token),
                'full_name' => $request->fullname
              ];
              $sendemail = Mail::to($request->email)->send(new VerifyEmail($data));
              return response()->json(new ValueMessage(['value'=>1,'message'=>'Register Success!','data'=> $success]),$this->successStatus);
            } catch (\Exception $e) {
              return response()->json(new ValueMessage(['value'=>0,'message'=>'Email failed to send!','data'=> $success]),$this->successStatus);
            }
        }
    }

    private function generate_random_string($length) {
      $characters = '0123456789qwertyuiopasdfghjklzxcvbnm';
      $characters_length = strlen($characters);
      $random_string = '';
      for ($i = 0; $i < $length; $i++) {
          $random_string .= $characters[rand(0, $characters_length - 1)];
      }
      return $random_string;
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
        if($request->has('expected_salary')){
            $change['expected_salary']=$request->expected_salary;
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

    public function resetPassword(Request $request){
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
                    ? redirect()->route('login')->with('status', __($status))
                    : back()->withErrors(['email' => [__($status)]]);
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
