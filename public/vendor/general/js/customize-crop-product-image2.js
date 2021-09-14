$(document).ready(function(){

  $image_crop_image2 = $('#image_demo_image2').croppie({
    enableExif: true,
    viewport: {
      width:250,
      height:250,
      type:'square' //circle or square
    },
    boundary:{
      width:300,
      height:300
    }
  });

  $('#upload_product_image2').on('change', function() {
    var reader = new FileReader();
    reader.onload = function (event) {
      $image_crop_image2.croppie('bind', {
        url: event.target.result
      }).then(function(){
        //console.log('jQuery bind complete');
      });
    }
    const filex = this.files[0];
    const fileType = filex['type'];
    const fileSize = filex['size']/1024/1024;
    //console.log(fileSize);
    const validImageTypes = ['image/gif', 'image/jpeg', 'image/jpg', 'image/png'];
    if (!validImageTypes.includes(fileType)) {
      // invalid file type code goes here.
      $('#uploadimageModalFalse').modal('show');
    } else {
      if (fileSize > 3) {
        $('#uploadimageModalFalseSize').modal('show');
      } else {
        reader.readAsDataURL(this.files[0]);
        $('#uploadimageModal_image2').modal('show');
      }
    }
  });

  $('.crop_image_image2').click(function(event){
    var link_url = document.URL;
    var parsing = link_url.split('/');
    var parsing_protocol = parsing[0];
    var parsing_mainurl = parsing[2];
    var parsing_language = parsing[3];

    var link_uri = window.location.pathname;
    var parsing2 = link_uri.split('/');
    const parsing_cpanel = parsing2[2];
    const parsing_menu = parsing2[3];

    var new_url_complete = parsing_protocol+'//'+parsing_mainurl+'/'+parsing_language+'/backend/modal';
    var m = $(this).attr("value");
    $image_crop_image2.croppie('result', {
      type: 'canvas',
      size: 'viewport'
    }).then(function(response){
      $.ajax({
        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
        url: new_url_complete,
        type: "POST",
        data:{image: response, id:m},
        success:function(data)
        {
          $('#uploadimageModal_image2').modal('hide');
          $('#uploaded_image2').html(data);
        }
      });
    })
  });
});
