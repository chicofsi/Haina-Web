$(document).ready(function() {

  var link_url = document.URL;
  var parsing = link_url.split('/');
  var parsing_protocol = parsing[0];
  var parsing_mainurl = parsing[2];
  var parsing_language = parsing[3];

  var link_uri = window.location.pathname;
  var parsing2 = link_uri.split('/');

  var new_url_complete = parsing_protocol+'//'+parsing_mainurl+'/'+parsing_language+'/backend/modal';

  $image_crop = $('#image_demo').croppie({
    enableExif: true,
    viewport: {
      width:250,
      height:250,
      type:'circle'
    },
    boundary:{
      width:300,
      height:300
    }
  });

  $('#upload_image').on('change', function() {
    var reader = new FileReader();
    reader.onload = function (event) {
      $image_crop.croppie('bind', {
        url: event.target.result
      }).then(function() {
        //console.log('');
      });
    }
    const filex = this.files[0];
    const fileType = filex['type'];
    const fileSize = filex['size']/1024/1024;
    const validImageTypes = ['image/gif', 'image/jpeg', 'image/jpg', 'image/png'];
    if (!validImageTypes.includes(fileType)) {
      $.ajax({
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        url: new_url_complete,
        type: "POST",
        data:{id:"format-file-not-found", fullurl_x:link_url},
        success:function(data) {
          $("#xcontainer").html(data);
          $("#xcontainer").modal('show',{backdrop: 'true'});
        }
      });
    } else {
      if (fileSize > 1) {
        $.ajax({
          headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
          url: new_url_complete,
          type: "POST",
          data:{id:"picture-size-too-large", fullurl_x:link_url},
          success:function(data) {
            $("#xcontainer").html(data);
            $("#xcontainer").modal('show',{backdrop: 'true'});
          }
        });
      } else {
        reader.readAsDataURL(this.files[0]);
        $('#upload-image').modal({backdrop: 'static', keyboard: false});
      }
    }
  });

  $('.crop_image').click(function(event) {
    var m = $(this).attr("value");
    $image_crop.croppie('result', {
      type: 'canvas',
      size: 'viewport'
    }).then(function(response) {
      $.ajax({
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        url: new_url_complete,
        type: "POST",
        data:{image: response, id:m, fullurl_x:link_url},
        success:function(data) {
          $('#upload-image').modal('hide');
          $('#uploaded_image').html(data);
        }
      });
    })
  });
});
