$(".modal-modal").click(function(e) {

  var link_url = document.URL;
  var parsing = link_url.split('/');
  var parsing_protocol = parsing[0];
  var parsing_mainurl = parsing[2];
  //var parsing_language = parsing[3];

  /*
  var link_uri = window.location.pathname;
  var parsing2 = link_uri.split('/');
  var parsing_cpanel = parsing2[2];
  var parsing_menu = parsing2[3];

  const queryString = window.location.search;
  const urlParams = new URLSearchParams(queryString);
  const get_max = urlParams.get('max');
  const get_page = urlParams.get('page');
  const get_search = urlParams.get('search');
  */

  var new_url_complete = parsing_protocol+'//'+parsing_mainurl+'/backend/modal';
  var m = $(this).attr("value");
  $("#modalloader").modal('show');

  $.ajax({
      headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
      url: new_url_complete,
      type: "POST",
      data : {id:m, fullurl_x:link_url},
      success: function (ajaxData){
          setTimeout(function()
          {
            $('#modalloader').modal('hide');
            $("#xcontainer").html(ajaxData);
            $("#xcontainer").modal('show',{backdrop: 'true'});
          }, 1000);
      }
  });

});
