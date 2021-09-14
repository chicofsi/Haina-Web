<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

// response
use App\Http\Controllers\Api\Response\ErrorController;
use App\Http\Controllers\Api\Response\SuccessController;

// use Models
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
    if (!isset($_GET['email']))
    return response()->json($this->ErrorController->error_400('incomplete parameters'));
    // inisialisasi
    $email = htmlentities(addslashes($_GET['email']));
    // chek
    $check = FixUsersModels::where('email', $email);
    if ($check->get()->count() < 1) return response()->json($this->ErrorController->error_404('email is not registered in our system'));
    // return to view
    return view('account.reset-password', [
      'email' => $email
    ]);
  }

  public function _post(Request $request) {
    if (!$request->filled('email') ||
        !$request->filled('old_password') ||
        !$request->filled('new_password') ||
        !$request->filled('repeat_password')) {return $this->notification('alert-danger', 'fa-exclamation-triangle', 'Incomplete parameters', ['old_password' => $request->old_password,'new_password' => $request->new_password,'repeat_password' => $request->repeat_password],request()->fullUrl());}
    // inisialisasi
    $email = htmlentities(addslashes($request->email));
    $old_password = htmlentities(addslashes($request->old_password));
    $new_password = htmlentities(addslashes($request->new_password));
    $repeat_password = htmlentities(addslashes($request->repeat_password));
    // chek
    $check = FixUsersModels::where('email', $email);
    if ($check->get()->count() < 1)
    return $this->notification('alert-danger', 'fa-exclamation-triangle', 'Email is not registered in our system', ['old_password' => $request->old_password,'new_password' => $request->new_password,'repeat_password' => $request->repeat_password],request()->fullUrl());
    // get password account
    $password_account = $check->first()['password'];
    // match hash
    $match_hash = Hash::check($old_password, $password_account);
    if ($match_hash != 1)
    return $this->notification('alert-danger', 'fa-exclamation-triangle', 'Password wrong', ['old_password' => $request->old_password,'new_password' => $request->new_password,'repeat_password' => $request->repeat_password],request()->fullUrl());
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
      return $this->notification('alert-success', 'fa-check', 'Your password has been updated', ['old_password' => '','new_password' => '','repeat_password' => ''],request()->fullUrl());
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

}
