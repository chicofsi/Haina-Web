<!DOCTYPE html>
<html lang="en"> <?php // style="background-color:#d5eaf1" ?>
<head>
  <meta charset="UTF-8">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">

  <title>Reset Password</title>

  <!-- favicon -->
  <link rel="shortcut icon" href="{{ url('/vendor/general') }}/img/haina-square.PNG" />
  <link rel="apple-touch-icon" href="{{ url('/vendor/general') }}/img/haina-square.PNG" />
  <link rel="apple-touch-icon" sizes="72x72" href="{{ url('/vendor/general') }}/img/haina-square.PNG" />
  <link rel="apple-touch-icon" sizes="114x114" href="{{ url('/vendor/general') }}/img/haina-square.PNG" />

  <!-- General CSS Files -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">

  <!-- CSS Libraries -->
  <link rel="stylesheet" href="{{ url('/vendor/stisla-master') }}/node_modules/bootstrap-social/bootstrap-social.css">

  <!-- Template CSS -->
  <link rel="stylesheet" href="{{ url('/vendor/stisla-master') }}/assets/css/style.css">
  <link rel="stylesheet" href="{{ url('/vendor/stisla-master') }}/assets/css/components.css">
</head>

<body> <?php // style="background-color:#d5eaf1" ?>
  <div id="app">
    <section class="section">
      <div class="container mt-5">
        <div class="row">
          <div class="col-12 col-sm-8 offset-sm-2 col-md-6 offset-md-3 col-lg-6 offset-lg-3 col-xl-4 offset-xl-4">
            <div class="card card-">
              <div class="login-brand">
                <?php // you can use this class for code below : class="shadow-light rounded-circle" ?>
                <img src="{{ url('/vendor/general') }}/img/haina.png" alt="logo" width="60%" style="padding:10px;margin-bottom:-40px">
              </div>
              <div class="card-body">
                @if(null !== (Session::get('session_message_reset_password')) and Session::get('session_message_reset_password') !== '')
                <div class="alert {{ Session::get('session_class_reset_password') }}">
                  <i class="fa {{ Session::get('session_icon_reset_password') }}" style="padding-right:10px"></i>
                  {{ Session::get('session_message_reset_password') }}
                </div>
                @endif
                <div class="alert alert-info">
                  <i class="fa fa-info-circle" style="padding-right:10px"></i>
                  Password must contain lowercase letters, uppercase letters, numbers and at least 8 characters
                </div>
                <form method="POST" class="needs-validation" novalidate="">
                  @csrf
                  <input type="hidden" name="email" value="{{ $email }}">
                  <div class="form-group">
                    <label for="old_password">Old Password</label>
                    <input type="password" class="form-control" name="old_password" tabindex="1" required id="old_password"autofocus
                    @if(null !== (Session::get('session_message_reset_password')) and Session::get('session_message_reset_password') !== '')
                    value="{{ Session::get('session_data_old_password') }}"
                    @endif
                    >
                    <div class="invalid-feedback">
                      Old password is required
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" class="form-control" name="new_password" tabindex="1" required id="new_password"
                    @if(null !== (Session::get('session_message_reset_password')) and Session::get('session_message_reset_password') !== '')
                    value="{{ Session::get('session_data_new_password') }}"
                    @endif
                    >
                    <div class="invalid-feedback">
                      New password is required
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="repeat_password">Repeat Password</label>
                    <input type="password" class="form-control" name="repeat_password" tabindex="1" required id="repeat_password"
                    @if(null !== (Session::get('session_message_reset_password')) and Session::get('session_message_reset_password') !== '')
                    value="{{ Session::get('session_data_repeat_password') }}"
                    @endif
                    >
                    <div class="invalid-feedback">
                      Repeat password is required
                    </div>
                  </div>
                  <div class="form-group" style="margin-top:-20px">
                    <label class="mt-0">
                      <input type="checkbox" name="custom-switch-checkbox" class="custom-switch-input" onclick="Toggle()">
                      <span class="custom-switch-indicator"></span>
                      <span class="custom-switch-description">Show Password</span>
                    </label>
                  </div>
                  <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-lg btn-block" tabindex="4">
                      Reset Password
                    </button>
                  </div>
                </form>
              </div>
            </div>
            <div class="mt-5 text-muted text-center">
              <a href="https://cv-haina-service-indonesia.business.site/" target="_blank">CV. Haina Service Indonesia</a>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  <div style="display: none;" aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" class="modal fade" id="xcontainer"></div>

  <!-- General JS Scripts -->
  <script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.nicescroll/3.7.6/jquery.nicescroll.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
  <script src="{{ url('/vendor/stisla-master') }}/assets/js/stisla.js"></script>

  <!-- JS Libraries -->
  <script src="{{ url('/vendor/general') }}/js/modal-no-loader.js" type="text/javascript"></script>

  <!-- Template JS File -->
  <script src="{{ url('/vendor/stisla-master') }}/assets/js/scripts.js"></script>
  <script src="{{ url('/vendor/stisla-master') }}/assets/js/custom.js"></script>

  <!-- Page Specific JS File -->
  <script type="text/javascript">
  function Toggle() {
    var old_password = document.getElementById("old_password");
    var new_password = document.getElementById("new_password");
    var repeat_password = document.getElementById("repeat_password");
    (old_password.type === "password") ? old_password.type = "text" : old_password.type = "password";
    (new_password.type === "password") ? new_password.type = "text" : new_password.type = "password";
    (repeat_password.type === "password") ? repeat_password.type = "text" : repeat_password.type = "password";
  }
  </script>
</body>
</html>

@php
session(['session_message_reset_password' => '']);
session(['session_data_old_password' => '']);
session(['session_data_new_password' => '']);
session(['session_data_repeat_password' => '']);
@endphp
