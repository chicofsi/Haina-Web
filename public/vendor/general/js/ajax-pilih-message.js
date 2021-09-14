$(".ajax-pilih-message").click(function(e) {

  var link_url = document.URL;
  var parsing = link_url.split('/');
  var parsing_protocol = parsing[0];
  var parsing_mainurl = parsing[2];

  var new_url_complete = parsing_protocol+'//'+parsing_mainurl+'/backend/modal-pilih-message';
  var m = $(this).attr("value");

  console.log(m);

  $.ajax({
      headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
      url: new_url_complete,
      type: "POST",
      data : {id:m, fullurl_x:link_url},
      success: function (ajaxData){
        $("#containerajaxpilihmessage").html(ajaxData);
      }
  });

});
