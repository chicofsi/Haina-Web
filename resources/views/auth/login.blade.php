
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="ThemeBucket">
    <link rel="shortcut icon" href="#" type="image/png">

    <title>Login</title>

    <link href="{{ url('vendor/adminex/adminex/adminex/html') }}/css/style.css" rel="stylesheet">
    <link href="{{ url('vendor/adminex/adminex/adminex/html') }}/css/style-responsive.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="js/html5shiv.js"></script>
    <script src="js/respond.min.js"></script>
    <![endif]-->
</head>

<body>

<div class="container">

    <form class="form-signin" method="POST" action="{{ route('login') }}">
        @csrf
        <div class="form-signin-heading text-center">
            <img style="max-width: 200px" src="{{asset('img/logohaina.png')}}" alt=""/>
        </div>
        <div class="login-wrap">
            <x-jet-input id="username" class="form-control" type="text" name="username" :value="old('username')" placeholder="Username" required autofocus />
            <x-jet-input id="password" class="form-control" type="password" name="password" placeholder="Password" required autocomplete="current-password" />

            <button class="btn btn-lg btn-login btn-block" type="submit">
                <i class="fa fa-check"></i>
            </button>




        </div>

    </form>

</div>



<!-- Placed js at the end of the document so the pages load faster -->

<!-- Placed js at the end of the document so the pages load faster -->
<script src="{{ url('vendor/adminex/adminex/adminex/html') }}/js/jquery-1.10.2.min.js"></script>
<script src="{{ url('vendor/adminex/adminex/adminex/html') }}/js/bootstrap.min.js"></script>
<script src="{{ url('vendor/adminex/adminex/adminex/html') }}/js/modernizr.min.js"></script>

</body>
</html>
