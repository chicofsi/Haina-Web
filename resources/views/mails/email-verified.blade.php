<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Email Verified | Haina Service Indonesia</title>

  <!-- General CSS Files -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">

  <!-- CSS Libraries -->

  <!-- Template CSS -->
  <link rel="stylesheet" href="{{ url('/vendor/stisla-master') }}/assets/css/style.css">
  <link rel="stylesheet" href="{{ url('/vendor/stisla-master') }}/assets/css/components.css">
</head>
<body class="layout-3">
  <div id="app">
    <div class="main-wrapper container">
      <!-- Main Content -->
      <div class="main-content">
        <section class="section">
          <div class="section-body">
            <div class="card">
              <div class="card-body">
                <h5 style="text-align:center">
                  <i class="fa {{ $icons }}" style="color:{{ $icons_color }}; padding-right:10px"></i>
                  {{ $title_headers }}
                </h5>
                <hr>
                <p style="text-align:center">
                  {{ $messages }}
                </p>
              </div>
            </div>
          </div>
        </section>
      </div>
      <footer class="main-footer">
        <div style="text-align:center">
          Copyright &copy; {{ date('Y') }} <div class="bullet"></div> <a href="https://cv-haina-service-indonesia.business.site/" target="_blank">Haina Service Indonesia</a>
        </div>
      </footer>
    </div>
  </div>
  <!-- General JS Scripts -->
  <script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.nicescroll/3.7.6/jquery.nicescroll.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
  <script src="{{ url('/vendor/stisla-master') }}/assets/js/stisla.js"></script>

  <!-- JS Libraies -->

  <!-- Page Specific JS File -->

  <!-- Template JS File -->
  <script src="{{ url('/vendor/stisla-master') }}/assets/js/scripts.js"></script>
  <script src="{{ url('/vendor/stisla-master') }}/assets/js/custom.js"></script>
</body>
</html>
