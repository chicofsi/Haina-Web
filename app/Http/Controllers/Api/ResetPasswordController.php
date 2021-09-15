<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

// response
use App\Http\Controllers\Api\Response\ErrorController;
use App\Http\Controllers\Api\Response\SuccessController;

// for email configuration
use App\Mail\VerifyEmail_ResetPassword;
use Illuminate\Support\Facades\Mail;

// use Models
use App\Models\EmailTokenModels;
use App\Models\FixUsersModels;

// use Laravel's built-in function
use Session;
use App;
use Carbon\Carbon;

class ResetPasswordController extends Controller {

  protected $ErrorController, $SuccessController;

  public function __construct(ErrorController $ErrorController, SuccessController $SuccessController) {
    $this->ErrorController = $ErrorController;
    $this->SuccessController = $SuccessController;
  }

  public function _get() {
    if (isset($_GET['email']) && !empty($_GET['email']) &&
        isset($_GET['token']) && !empty($_GET['token'])) {
      // inisialisasi
      $email = htmlentities(addslashes($_GET['email']));
      $token = htmlentities(addslashes($_GET['token']));
      // check
      $check = EmailTokenModels::where('email', $email)
                               ->where('token', $token)
                               ->where('deleted_at', NULL);
      if ($check->get()->count() < 1) return response()->json($this->ErrorController->error_404('your email and token access is wrong'));
      // get field
      $ids = $check->first()['ids'];
      $valid_until = $check->first()['valid_until'];
      // set date now
      $date_now = date('Y-m-d H:i:s', strtotime(Carbon::now()));
      // check condition valid until
      if ($date_now > $valid_until) return response()->json($this->ErrorController->error_404('token has expired'));
      // return to view
      return view('account.reset-password', [
        'email' => $email,
        'token' => $token
      ]);
    } elseif (isset($_GET['email']) && !empty($_GET['email'])) {
      // inisialisasi
      $email = htmlentities(addslashes($_GET['email']));
      // cek apakah email terdaftar di database
      $check_email_users = FixUsersModels::where('email', $email);
      if ($check_email_users->get()->count() < 1) return response()->json($this->ErrorController->error_404('email is not registered in our system'));
      // cek apakah token sudah terdaftar di database
      $check_email_tokens = EmailTokenModels::where('email', $email);
      if ($check_email_tokens->get()->count() < 1) {
        // email dan token belum terdaftar
        return $this->sending_email_and_save_token($email);
      } else {
        // email dan token sudah terdaftar
        // check highest created_at value
        $check_highest = EmailTokenModels::where('email', $email)
                                         ->where('deleted_at', NULL)
                                         ->orderBy('created_at', 'DESC')
                                         ->take(1);
        $created_at = $check_highest->first()['created_at'];
        // cek apakah email sudah pernah dikirim dalam 1 menit yang lalu
        // cek nilai created_at
        $created_at_plus1 = date('Y-m-d H:i:s', strtotime('+1 minutes', strtotime($created_at)));
        $fix_carbon_now = date('Y-m-d H:i:s', strtotime(Carbon::now()));
        if ($fix_carbon_now <= $created_at_plus1)
        return response()->json($this->ErrorController->error_403('You have to wait for 1 minute to be able to send reset password request again'));
        // buat token baru dan kirimkan kembali email permintaan reset password
        return $this->sending_email_and_save_token($email);
      }
    } else {
      return response()->json($this->ErrorController->error_400('incomplete parameters'));
    }
  }

  public function _post(Request $request) {
    if (!$request->filled('email') ||
        !$request->filled('token_user') ||
        !$request->filled('new_password') ||
        !$request->filled('repeat_password')) {return $this->notification('alert-danger', 'fa-exclamation-triangle', 'Incomplete parameters', ['old_password' => $request->old_password,'new_password' => $request->new_password,'repeat_password' => $request->repeat_password],request()->fullUrl());}
    // inisialisasi
    $email = htmlentities(addslashes($request->email));
    $token_user = htmlentities(addslashes($request->token_user));
    $new_password = htmlentities(addslashes($request->new_password));
    $repeat_password = htmlentities(addslashes($request->repeat_password));
    // check email and token
    $check_tokens = EmailTokenModels::where('email', $email)
                                    ->where('token', $token_user)
                                    ->where('deleted_at', NULL);
    if ($check_tokens->get()->count() < 1) return response()->json($this->ErrorController->error_404('your email and token access is wrong'));
    // check token expired
    $valid_until = $check_tokens->first()['valid_until'];
    $date_now = date('Y-m-d H:i:s', strtotime(Carbon::now()));
    if ($date_now > $valid_until) return response()->json($this->ErrorController->error_404('token has expired'));
    // check users
    $check = FixUsersModels::where('email', $email);
    if ($check->get()->count() < 1)
    return $this->notification('alert-danger', 'fa-exclamation-triangle', 'Email is not registered in our system', ['old_password' => $request->old_password,'new_password' => $request->new_password,'repeat_password' => $request->repeat_password],request()->fullUrl());
    /*
    // get password account
    $password_account = $check->first()['password'];
    // match hash
    $match_hash = Hash::check($old_password, $password_account);
    if ($match_hash != 1)
    return $this->notification('alert-danger', 'fa-exclamation-triangle', 'Password wrong', ['old_password' => $request->old_password,'new_password' => $request->new_password,'repeat_password' => $request->repeat_password],request()->fullUrl());
    */
    // compare new password
    if ($new_password != $repeat_password)
    return $this->notification('alert-danger', 'fa-exclamation-triangle', "New password doesn't match", ['old_password' => $request->old_password,'new_password' => $request->new_password,'repeat_password' => $request->repeat_password],request()->fullUrl());
    // regex for password
    $uppercase = preg_match('@[A-Z]@', $new_password);
    $lowercase = preg_match('@[a-z]@', $new_password);
    $number = preg_match('@[0-9]@', $new_password);
    if (!$uppercase || !$lowercase || !$number || strlen($new_password) < 8)
    return $this->notification('alert-danger', 'fa-exclamation-triangle', 'Your password does not meet our rules. Please pay attention to the password requirements that must be met.', ['old_password' => $request->old_password,'new_password' => $request->new_password,'repeat_password' => $request->repeat_password],request()->fullUrl());
    // hash password
    $hash_new_password = Hash::make($new_password);
    // update
    $update = FixUsersModels::where('email', $email)
                            ->update([
                              'password' => $hash_new_password,
                              'updated_at' => Carbon::now()
                            ]);
    if (isset($update) && $update == '1') {
      // return $this->notification('alert-success', 'fa-check', 'Your password has been updated', ['old_password' => '','new_password' => '','repeat_password' => ''],request()->fullUrl());
      $update_token = EmailTokenModels::where('email', $email)
                                      ->update([
                                        'deleted_at' => Carbon::now()
                                      ]);
      return response()->json($this->SuccessController->success_201('password changed successfully', ''));
    } else {
      return $this->notification('alert-danger', 'fa-exclamation-triangle', 'Oops! an error occurred while the system was updating your password. Contact the administrator if this problem still occurs.', ['old_password' => $request->old_password,'new_password' => $request->new_password,'repeat_password' => $request->repeat_password],request()->fullUrl());
    }
  }

  private function notification($class, $icon, $message, $data, $url) {
    session([
      'session_class_reset_password' => $class,
      'session_icon_reset_password' => $icon,
      'session_message_reset_password' => $message,
      'session_data_old_password' => $data['old_password'],
      'session_data_new_password' => $data['new_password'],
      'session_data_repeat_password' => $data['repeat_password']
    ]);
    return redirect(url($url));
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

  private function sending_email_and_save_token($email) {
    // save to email_tokens
    // create ids
    $ids = $this->generate_random_string(64);
    // token
    $fix_token = $this->generate_random_string(64);
    // current time
    $now = Carbon::now();
    $fix_now = date('Y-m-d H:i:s', strtotime($now));
    // set valid_until
    $valid_until = date('Y-m-d H:i:s', strtotime('+1 hours', strtotime($fix_now)));
    // save
    $create = EmailTokenModels::create([
      'ids' => $ids,
      'email' => $email,
      'token' => $fix_token,
      'valid_until' => $valid_until,
      'created_at' => $now,
      'updated_at' => $now,
      'deleted_at' => null
    ]);
    $data = [
      'email_destination' => $email,
      'fix_token' => $fix_token,
      'url_reset_password' => url('/reset-password?email='.$email.'&token='.$fix_token)
    ];
    $sendemail = Mail::to($email)->send(new VerifyEmail_ResetPassword($data));
    return response()->json($this->SuccessController->success_201('Reset password request has been sent to destination email!', ''));
  }

}
