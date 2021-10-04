<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\ServiceAdmin;

use Illuminate\Support\Facades\Auth;

use App\Models\AdminLogs;

use Cookie;

class LoginController extends Controller
{
    public function getLogin()
  	{
  		if(Auth::guard('admin')->check()){
      	return redirect()->intended('/dashboard');
  		}else if(Auth::guard('service_admin')->check()){
  			return redirect()->intended('/service-dashboard');
  		}
      // check privacy policy
      if (Cookie::get('privacy_policy') === null) {
        $privacy_policy = 'no';
      } else {
        $privacy_policy = 'yes';
      }
    	return view('auth.login', [
        'privacy_policy' => $privacy_policy
      ]);
  	}

  	public function postLogin(Request $request)
  	{

      	// Validate the form data
    	$this->validate($request, [
	      'username' => 'required',
	      'password' => 'required'
    	]);

    	if (Auth::guard('admin')->attempt(['username' => $request->username, 'password' => $request->password])) {

        AdminLogs::create([
           'id_admin' => Auth::id(),
           'id_admin_activity' => 1,
           'message' => 'Admin successfully login'
        ]);

  			return redirect()->intended('/dashboard');
    	} else if (Auth::guard('service_admin')->attempt(['username' => $request->username, 'password' => $request->password])) {
  			return redirect()->intended('/service-dashboard');
    	}

      return redirect()->intended('/login');


  	}

    public function logout( Request $request )
    {
      AdminLogs::create([
           'id_admin' => Auth::id(),
           'id_admin_activity' => 2,
           'message' => 'Admin logout'
        ]);

      Auth::logout();

      return redirect()->intended('/login');
    }
}
