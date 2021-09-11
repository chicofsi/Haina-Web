<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// response
use App\Http\Controllers\Api\Response\ErrorController;
use App\Http\Controllers\Api\Response\SuccessController;

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
    return response()->json($this->ErrorController->error_400('incomplete parameters'));
    // inisialisasi
    $email_user = htmlentities(addslashes($_GET['user']));
    $token = htmlentities(addslashes($_GET['token']));
    // chek
    $check = EmailTokenModels::where('email', $email_user)
                             ->where('token', $token)
                             ->where('deleted_at', NULL);
    if ($check->get()->count() < 1) return response()->json($this->ErrorController->error_404('your email and token access is wrong'));
    // get field
    $ids = $check->first()['ids'];
    $valid_until = $check->first()['valid_until'];
    // set date now
    $date_now = date('Y-m-d h:i:s');
    // check condition valid until
    if ($date_now > $valid_until) return response()->json($this->ErrorController->error_404('token has expired'));
    // update field email_verified_at
    $update = FixUsersModels::where('email', $email_user)
                            ->update([
                              'email_verified_at' => Carbon::now(),
                              'updated_at' => Carbon::now()
                            ]);
    return (isset($update) && $update == '1') ? response()->json($this->SuccessController->success_201('your email has been verified', '')) : response()->json($this->ErrorController->error_500('an error occurred while verifying your email. contact administrator to fix this problem'));
  }

}
