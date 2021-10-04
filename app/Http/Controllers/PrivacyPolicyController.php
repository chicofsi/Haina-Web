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
    if (isset($_GET['endpoint'])) {
      if ($_GET['endpoint'] == 'policy')
      // redirect to
      return redirect(url('/policy'));
    } else {
      // redirect to
      return redirect(url('/login'));
    }
  }

  public function get_policy() {
    // check privacy policy
    if (Cookie::get('privacy_policy') === null) {
      $privacy_policy = 'no';
    } else {
      $privacy_policy = 'yes';
    }
    return view('policy.policy', [
      'privacy_policy' => $privacy_policy
    ]);
  }

}
