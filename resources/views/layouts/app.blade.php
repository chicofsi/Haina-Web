
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <meta name="author" content="chico"/>

        <title>{{ config('app.name', 'Laravel') }}</title>


        <!-- Favicon -->
        <link rel="icon" href="{{ asset('/img/logo_icon.png') }}" type="image/x-icon"/>


        <!--icheck-->
<<<<<<< Updated upstream
        <link href="{{ url('vendor/adminex/adminex/adminex/html') }}/js/iCheck/skins/minimal/minimal.css" rel="stylesheet">
        <link href="{{ url('vendor/adminex/adminex/adminex/html') }}/js/iCheck/skins/square/square.css" rel="stylesheet">
        <link href="{{ url('vendor/adminex/adminex/adminex/html') }}/js/iCheck/skins/square/red.css" rel="stylesheet">
        <link href="{{ url('vendor/adminex/adminex/adminex/html') }}/js/iCheck/skins/square/blue.css" rel="stylesheet">

        <!--dashboard calendar-->
        <link href="{{ url('vendor/adminex/adminex/adminex/html') }}/css/clndr.css" rel="stylesheet">

        <!--Morris Chart CSS -->
        <link rel="stylesheet" href="{{ url('vendor/adminex/adminex/adminex/html') }}/js/morris-chart/morris.css">

        <link rel="stylesheet" href="{{ url('vendor/adminex/adminex/adminex/html') }}/js/data-tables/DT_bootstrap.css" />

        <link  href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" rel="stylesheet">

        <!--common-->
        <link href="{{ url('vendor/adminex/adminex/adminex/html') }}/css/style.css" rel="stylesheet">
        <link href="{{ url('vendor/adminex/adminex/adminex/html') }}/css/style-responsive.css" rel="stylesheet">
=======
        <link href="{{ url('/vendor/adminex/html') }}/js/iCheck/skins/minimal/minimal.css" rel="stylesheet">
        <link href="{{ url('/vendor/adminex/html') }}/js/iCheck/skins/square/square.css" rel="stylesheet">
        <link href="{{ url('/vendor/adminex/html') }}/js/iCheck/skins/square/red.css" rel="stylesheet">
        <link href="{{ url('/vendor/adminex/html') }}/js/iCheck/skins/square/blue.css" rel="stylesheet">

        <!--dashboard calendar-->
        <link href="{{ url('/vendor/adminex/html') }}/css/clndr.css" rel="stylesheet">

        <!--Morris Chart CSS -->
        <link rel="stylesheet" href="{{ url('/vendor/adminex/html') }}/js/morris-chart/morris.css">

        <link rel="stylesheet" href="{{ url('/vendor/adminex/html') }}/js/data-tables/DT_bootstrap.css" />

        <link  href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" rel="stylesheet">


        <link href="{{ url('/css/all.css') }}" rel="stylesheet">

        <!--common-->
        <link href="{{ url('/vendor/adminex/html') }}/css/style.css" rel="stylesheet">
        <link href="{{ url('/vendor/adminex/html') }}/css/style-responsive.css" rel="stylesheet">
>>>>>>> Stashed changes

        @livewireStyles



        @yield('style')
    </head>

    <body class="sticky-header">

        @livewire('side-bar')

        <div class="main-content" >


            @livewire('header')


            {{ $slot }}


            <!--footer section start-->
            @livewire('footer')
            <!--footer section end-->
        </div>
        <!-- main content end-->




        <!-- Placed js at the end of the document so the pages load faster -->
<<<<<<< Updated upstream
        <script src="{{ url('vendor/adminex/adminex/adminex/html') }}/js/jquery-1.10.2.min.js"></script>
        <script src="{{ url('vendor/adminex/adminex/adminex/html') }}/js/jquery-ui-1.9.2.custom.min.js"></script>
        <script src="{{ url('vendor/adminex/adminex/adminex/html') }}/js/jquery-migrate-1.2.1.min.js"></script>
        <script src="{{ url('vendor/adminex/adminex/adminex/html') }}/js/bootstrap.min.js"></script>
        <script src="{{ url('vendor/adminex/adminex/adminex/html') }}/js/modernizr.min.js"></script>
        <script src="{{ url('vendor/adminex/adminex/adminex/html') }}/js/jquery.nicescroll.js"></script>

        <!--easy pie chart-->
        <script src="{{ url('vendor/adminex/adminex/adminex/html') }}/js/easypiechart/jquery.easypiechart.js"></script>
        <script src="{{ url('vendor/adminex/adminex/adminex/html') }}/js/easypiechart/easypiechart-init.js"></script>

        <!--Sparkline Chart-->
        <script src="{{ url('vendor/adminex/adminex/adminex/html') }}/js/sparkline/jquery.sparkline.js"></script>
        <script src="{{ url('vendor/adminex/adminex/adminex/html') }}/js/sparkline/sparkline-init.js"></script>

        <!--icheck -->
        <script src="{{ url('vendor/adminex/adminex/adminex/html') }}/js/iCheck/jquery.icheck.js"></script>
        <script src="{{ url('vendor/adminex/adminex/adminex/html') }}/js/icheck-init.js"></script>

        <!-- jQuery Flot Chart-->
        <script src="{{ url('vendor/adminex/adminex/adminex/html') }}/js/flot-chart/jquery.flot.js"></script>
        <script src="{{ url('vendor/adminex/adminex/adminex/html') }}/js/flot-chart/jquery.flot.tooltip.js"></script>
        <script src="{{ url('vendor/adminex/adminex/adminex/html') }}/js/flot-chart/jquery.flot.resize.js"></script>


        <!--Morris Chart-->
        <script src="{{ url('vendor/adminex/adminex/adminex/html') }}/js/morris-chart/morris.js"></script>
        <script src="{{ url('vendor/adminex/adminex/adminex/html') }}/js/morris-chart/raphael-min.js"></script>

        <!--Calendar-->
        <script src="{{ url('vendor/adminex/adminex/adminex/html') }}/js/calendar/clndr.js"></script>
        <script src="{{ url('vendor/adminex/adminex/adminex/html') }}/js/calendar/evnt.calendar.init.js"></script>
        <script src="{{ url('vendor/adminex/adminex/adminex/html') }}/js/calendar/moment-2.2.1.js"></script>
        <script src="http://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.5.2/underscore-min.js"></script>

        <!--common scripts for all pages-->
        <script src="{{ url('vendor/adminex/adminex/adminex/html') }}/js/scripts.js"></script>

        <!--Dashboard Charts-->
        <script src="{{ url('vendor/adminex/adminex/adminex/html') }}/js/dashboard-chart-init.js"></script>

        <!--dynamic table-->
        {{-- <script type="text/javascript" language="javascript" src="{{ url('vendor/adminex/adminex/adminex/html') }}/js/advanced-datatable/js/jquery.dataTables.js"></script>
        <script type="text/javascript" src="{{ url('vendor/adminex/adminex/adminex/html') }}/js/data-tables/DT_bootstrap.js"></script> --}}
=======
        <script src="{{ url('/vendor/adminex/html') }}/js/jquery-1.10.2.min.js"></script>
        <script src="{{ url('/vendor/adminex/html') }}/js/jquery-ui-1.9.2.custom.min.js"></script>
        <script src="{{ url('/vendor/adminex/html') }}/js/jquery-migrate-1.2.1.min.js"></script>
        <script src="{{ url('/vendor/adminex/html') }}/js/bootstrap.min.js"></script>
        <script src="{{ url('/vendor/adminex/html') }}/js/modernizr.min.js"></script>
        <script src="{{ url('/vendor/adminex/html') }}/js/jquery.nicescroll.js"></script>

        <!--easy pie chart-->
        <script src="{{ url('/vendor/adminex/html') }}/js/easypiechart/jquery.easypiechart.js"></script>
        <script src="{{ url('/vendor/adminex/html') }}/js/easypiechart/easypiechart-init.js"></script>

        <!--Sparkline Chart-->
        <script src="{{ url('/vendor/adminex/html') }}/js/sparkline/jquery.sparkline.js"></script>
        <script src="{{ url('/vendor/adminex/html') }}/js/sparkline/sparkline-init.js"></script>

        <!--icheck -->
        <script src="{{ url('/vendor/adminex/html') }}/js/iCheck/jquery.icheck.js"></script>
        <script src="{{ url('/vendor/adminex/html') }}/js/icheck-init.js"></script>

        <!-- jQuery Flot Chart-->
        <script src="{{ url('/vendor/adminex/html') }}/js/flot-chart/jquery.flot.js"></script>
        <script src="{{ url('/vendor/adminex/html') }}/js/flot-chart/jquery.flot.tooltip.js"></script>
        <script src="{{ url('/vendor/adminex/html') }}/js/flot-chart/jquery.flot.resize.js"></script>


        <!--Morris Chart-->
        <script src="{{ url('/vendor/adminex/html') }}/js/morris-chart/morris.js"></script>
        <script src="{{ url('/vendor/adminex/html') }}/js/morris-chart/raphael-min.js"></script>

        <!--Calendar-->
        <script src="{{ url('/vendor/adminex/html') }}/js/calendar/clndr.js"></script>
        <script src="{{ url('/vendor/adminex/html') }}/js/calendar/evnt.calendar.init.js"></script>
        <script src="{{ url('/vendor/adminex/html') }}/js/calendar/moment-2.2.1.js"></script>
        <script src="http://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.5.2/underscore-min.js"></script>

        <!--common scripts for all pages-->
        <script src="{{ url('/vendor/adminex/html') }}/js/scripts.js"></script>

        <!--Dashboard Charts-->
        <script src="{{ url('/vendor/adminex/html') }}/js/dashboard-chart-init.js"></script>

        <!--dynamic table-->
        {{-- <script type="text/javascript" language="javascript" src="{{ url('/vendor/adminex/html') }}/js/advanced-datatable/js/jquery.dataTables.js"></script>
        <script type="text/javascript" src="{{ url('/vendor/adminex/html') }}/js/data-tables/DT_bootstrap.js"></script> --}}
>>>>>>> Stashed changes
        <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>


        @yield('script')
    </body>

</html>
