<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// response
use App\Http\Controllers\Api\Response\ErrorController;
use App\Http\Controllers\Api\Response\SuccessController;

// for email configuration
use App\Mail\VerifyEmail;
use Illuminate\Support\Facades\Mail;

// use Models
use App\Models\EmailTokenModels;
use App\Models\FixUsersModels;

// use Laravel's built-in function
use Session;
use App;
use Carbon\Carbon;

class EmailVerifiedController extends Controller {

  protected $ErrorController, $SuccessController;

  public function __construct(ErrorController $ErrorController, SuccessController $SuccessController) {
    $this->ErrorController = $ErrorController;
    $this->SuccessController = $SuccessController;
  }

  public function verified_get() {
    if(!isset($_GET['user']) || !isset($_GET['token']))
    // return response()->json($this->ErrorController->error_400('incomplete parameters'));
    return view('mails.email-verified', [
      'response' => 'error',
      'icons' => 'fa-times',
      'icons_color' => '#ef3333',
      'title_headers' => 'Oops !!',
      'messages' => 'Error 400 : Incomplete parameters',
    ]);
    // inisialisasi
    $email_user = htmlentities(addslashes($_GET['user']));
    $token = htmlentities(addslashes($_GET['token']));
    // chek
    $check = EmailTokenModels::where('email', $email_user)
                             ->where('token', $token)
                             ->where('deleted_at', NULL);
    if ($check->get()->count() < 1)
    // return response()->json($this->ErrorController->error_404('your email and token access is wrong'));
    return view('mails.email-verified', [
      'response' => 'error',
      'icons' => 'fa-times',
      'icons_color' => '#ef3333',
      'title_headers' => 'Oops !!',
      'messages' => 'Error 404 : Your email and token access is wrong',
    ]);
    // get field
    $ids = $check->first()['ids'];
    $valid_until = $check->first()['valid_until'];
    // set date now
    $date_now = date('Y-m-d H:i:s');
    // check condition valid until
    if ($date_now > $valid_until)
    // return response()->json($this->ErrorController->error_404('token has expired'));
    return view('mails.email-verified', [
      'response' => 'error',
      'icons' => 'fa-times',
      'icons_color' => '#ef3333',
      'title_headers' => 'Oops !!',
      'messages' => 'Error 404 : Token has expired',
    ]);
    // update field email_verified_at
    $update = FixUsersModels::where('email', $email_user)
                            ->update([
                              'email_verified_at' => Carbon::now(),
                              'updated_at' => Carbon::now()
                            ]);
    if (isset($update) && $update == '1') {
      // return response()->json($this->SuccessController->success_201('your email has been verified', ''));
      return view('mails.email-verified', [
        'response' => 'succes',
        'icons' => 'fa-check',
        'icons_color' => '#61ef33',
        'title_headers' => 'Success',
        'messages' => 'Your email has been verified',
      ]);
    } else {
      // response()->json($this->ErrorController->error_500('an error occurred while verifying your email. contact administrator to fix this problem'));
      return view('mails.email-verified', [
        'response' => 'error',
        'icons' => 'fa-times',
        'icons_color' => '#ef3333',
        'title_headers' => 'Oops !!',
        'messages' => 'Error 500 : An error occurred while verifying your email. Contact administrator to fix this problem',
      ]);
    }
  }

  public function resend_verified_get() {
    if(!isset($_GET['user']) ||
       !isset($_GET['name']) ||
       empty($_GET['user']) ||
       empty($_GET['name']))
    return response()->json($this->ErrorController->error_400('incomplete parameters'));
    // inisialisasi
    $email_user = htmlentities(addslashes($_GET['user']));
    $fullname = htmlentities(addslashes($_GET['name']));
    // chek
    $check = FixUsersModels::where('email', $email_user);
    if ($check->get()->count() < 1) return response()->json($this->ErrorController->error_404('email is not registered in our system'));
    // check highest created_at value
    $check_highest = EmailTokenModels::where('email', $email_user)
                                     ->where('deleted_at', NULL)
                                     ->orderBy('created_at', 'DESC')
                                     ->take(1);
    $created_at = $check_highest->first()['created_at'];
    // cek apakah email sudah pernah dikirim dalam 1 menit yang lalu
    // cek nilai created_at
    $created_at_plus1 = date('Y-m-d H:i:s', strtotime('+1 minutes', strtotime($created_at)));
    $fix_carbon_now = date('Y-m-d H:i:s', strtotime(Carbon::now()));
    if ($fix_carbon_now <= $created_at_plus1)
    return response()->json($this->ErrorController->error_403('You have to wait for 1 minute to be able to resend email verified request again'));
    // try sending email
    try {
      // save to email_tokens
      // create ids
      $ids = $this->generate_random_string(64);
      // token
      $fix_token = $this->generate_random_string(64);
      // current time
      $now = date('Y-m-d H:i:s');
      // set valid_until
      $valid_until = date('Y-m-d H:i:s', strtotime('+1 hours', strtotime($now)));
      // save
      $create = EmailTokenModels::create([
        'ids' => $ids,
        'email' => $email_user,
        'token' => $fix_token,
        'valid_until' => $valid_until,
        'created_at' => $now,
        'updated_at' => $now,
        'deleted_at' => null
      ]);
      $data = [
        'email_destination' => $email_user,
        'fix_token' => $fix_token,
        'url_activation' => url('/email-verified?user='.$email_user.'&token='.$fix_token),
        'full_name' => $fullname
      ];
      $sendemail = Mail::to($email_user)->send(new VerifyEmail($data));
      return response()->json($this->SuccessController->success_201('Resend email verified succes!', ''));
    } catch (\Exception $e) {
      return response()->json($this->ErrorController->error_404('Email failed to send!'));
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

}
