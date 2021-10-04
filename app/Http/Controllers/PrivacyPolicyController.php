<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// use Laravel's built-in function
use Cookie;
use App;

class PrivacyPolicyController extends Controller {

  public function accept_terms_and_condition() {
    // set cookie
    Cookie::queue('privacy_policy', 'i_accept_terms_and_conditions', 518400);
    // redirect to
    return redirect(url('/login'));
  }

}
