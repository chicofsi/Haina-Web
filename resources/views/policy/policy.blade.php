
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <meta name="description" content="Privacy Policy Haina Service Indonesia">

    <!-- favicon -->
    <link rel="shortcut icon" href="{{ url('/vendor/general') }}/img/haina-square.PNG" />
    <link rel="apple-touch-icon" href="{{ url('/vendor/general') }}/img/haina-square.PNG" />
    <link rel="apple-touch-icon" sizes="72x72" href="{{ url('/vendor/general') }}/img/haina-square.PNG" />
    <link rel="apple-touch-icon" sizes="114x114" href="{{ url('/vendor/general') }}/img/haina-square.PNG" />

    <title>Privacy Policy, Terms and Conditions | Haina Service Indonesia</title>

    <link href="{{ url('/vendor/adminex/adminex/adminex/html/') }}/css/style.css" rel="stylesheet">
    <link href="{{ url('/vendor/adminex/adminex/adminex/html/') }}/css/style-responsive.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="js/html5shiv.js"></script>
    <script src="js/respond.min.js"></script>
    <![endif]-->
    <style media="screen">
    .scroll{
      overflow: scroll;
      height: 450px;
      overflow-x: hidden;
      /*script tambahan khusus untuk IE */
      scrollbar-face-color: #CE7E00;
      scrollbar-shadow-color: #FFFFFF;
      scrollbar-highlight-color: #6F4709;
      scrollbar-3dlight-color: #11111;
      scrollbar-darkshadow-color: #6F4709;
      scrollbar-track-color: #FFE8C1;
      scrollbar-arrow-color: #6F4709;
    }
    </style>
</head>

<body>

<div class="container">

</div>



<!-- Placed js at the end of the document so the pages load faster -->

<!-- Placed js at the end of the document so the pages load faster -->
<script src="{{ url('/vendor/adminex/adminex/adminex/html/') }}/js/jquery-1.10.2.min.js"></script>
<script src="{{ url('/vendor/adminex/adminex/adminex/html/') }}/js/bootstrap.min.js"></script>
<script src="{{ url('/vendor/adminex/adminex/adminex/html/') }}/js/modernizr.min.js"></script>

<script type="text/javascript">
  $(document).ready(function(){
    $("#myprivacypolicy").modal('show');
  });
</script>
<div class="modal fade" id="myprivacypolicy" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog">
    <div class="modal-content" style="border-radius:10px">
      <div class="modal-header" style="border-top-left-radius:10px;border-top-right-radius:10px">
        <div class="modal-title">
          <h4 style="text-align:center">Terms and Conditions</h4>
        </div>
      </div>
      <div class="modal-body">
        @if ($privacy_policy == 'yes')
        <!-- Alert from here -->
        <div class="alert alert-info fade in">
          <strong>Thank you!</strong>
          You have accepted the terms and conditions of use of our system
        </div>
        <!-- Alert until here -->
        @endif
        <h4 style="font-weight:bold">Privacy Policy for Haina Service Indonesia</h4>

        <p>At Haina Service Indonesia, accessible from https://hainaservice.com/, one of our main priorities is the privacy of our visitors. This Privacy Policy document contains types of information that is collected and recorded by Haina Service Indonesia and how we use it.</p>

        <p>If you have additional questions or require more information about our Privacy Policy, do not hesitate to contact us.</p>

        <h4 style="font-weight:bold">Log Files</h4>

        <p>Haina Service Indonesia follows a standard procedure of using log files. These files log visitors when they visit websites. All hosting companies do this and a part of hosting services' analytics. The information collected by log files include internet protocol (IP) addresses, browser type, Internet Service Provider (ISP), date and time stamp, referring/exit pages, and possibly the number of clicks. These are not linked to any information that is personally identifiable. The purpose of the information is for analyzing trends, administering the site, tracking users' movement on the website, and gathering demographic information. Our Privacy Policy was created with the help of the <a href="https://www.privacypolicyonline.com/privacy-policy-generator/">Privacy Policy Generator</a>.</p>

        <h4 style="font-weight:bold">Cookies and Web Beacons</h4>

        <p>Like any other website, Haina Service Indonesia uses 'cookies'. These cookies are used to store information including visitors' preferences, and the pages on the website that the visitor accessed or visited. The information is used to optimize the users' experience by customizing our web page content based on visitors' browser type and/or other information.</p>

        <p>For more general information on cookies, please read <a href="https://www.generateprivacypolicy.com/#cookies">"Cookies" article from the Privacy Policy Generator</a>.</p>

        <h4 style="font-weight:bold">Privacy Policies</h4>

        <P>You may consult this list to find the Privacy Policy for each of the advertising partners of Haina Service Indonesia.</p>

        <p>Third-party ad servers or ad networks uses technologies like cookies, JavaScript, or Web Beacons that are used in their respective advertisements and links that appear on Haina Service Indonesia, which are sent directly to users' browser. They automatically receive your IP address when this occurs. These technologies are used to measure the effectiveness of their advertising campaigns and/or to personalize the advertising content that you see on websites that you visit.</p>

        <p>Note that Haina Service Indonesia has no access to or control over these cookies that are used by third-party advertisers.</p>

        <h4 style="font-weight:bold">Third Party Privacy Policies</h4>

        <p>Haina Service Indonesia's Privacy Policy does not apply to other advertisers or websites. Thus, we are advising you to consult the respective Privacy Policies of these third-party ad servers for more detailed information. It may include their practices and instructions about how to opt-out of certain options. </p>

        <p>You can choose to disable cookies through your individual browser options. To know more detailed information about cookie management with specific web browsers, it can be found at the browsers' respective websites. What Are Cookies?</p>

        <h4 style="font-weight:bold">Children's Information</h4>

        <p>Another part of our priority is adding protection for children while using the internet. We encourage parents and guardians to observe, participate in, and/or monitor and guide their online activity.</p>

        <p>Haina Service Indonesia does not knowingly collect any Personal Identifiable Information from children under the age of 13. If you think that your child provided this kind of information on our website, we strongly encourage you to contact us immediately and we will do our best efforts to promptly remove such information from our records.</p>

        <h4 style="font-weight:bold">Online Privacy Policy Only</h4>

        <p>This Privacy Policy applies only to our online activities and is valid for visitors to our website with regards to the information that they shared and/or collect in Haina Service Indonesia. This policy is not applicable to any information collected offline or via channels other than this website.</p>

        <h4 style="font-weight:bold">Consent</h4>

        <p>By using our website, you hereby consent to our Privacy Policy and agree to its Terms and Conditions.</p>
      </div>
      @if ($privacy_policy == 'no')
      <div class="modal-footer">
        <a href="{{ url('/accept-terms-and-conditions?endpoint=policy') }}" class="btn btn-info">Accept</a>
      </div>
      @endif
    </div>
  </div>
</div>

</body>
</html>
