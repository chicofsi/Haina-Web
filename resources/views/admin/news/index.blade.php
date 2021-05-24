<x-app-layout>
    @section('style')
    <style type="text/css">
        .img-row{
            max-width: 100px; 
        }
    </style>
    @endsection
    <!-- page heading start-->
    <div class="page-heading">
        <h3>
            Manage News
        </h3>
        <ul class="breadcrumb">
            <li>
                <a href="{{route('dashboard')}}">Dashboard</a>
            </li>
            <li class="active"> Manage News </li>
        </ul>
    </div>
    <!-- page heading end-->

    <!--body wrapper start-->
    <div class="wrapper">
        <div class="row">
            <div class="col-sm-12">
                <section class="panel">
                    <header class="panel-heading">
                        Manage News
                        <span class="pull-right">
                            <a href="javascript:void(0);" onclick="add()" class=" btn btn-primary btn-sm">Add News  <i class="fa fa-plus"></i></a>
                            {{-- <a href="javascript:;" class="fa fa-times"></a> --}}
                         </span>
                    </header>
                    <div class="panel-body">
                        <div class="adv-table">
                            <table  class="display table  table-striped" id="news_table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Photo</th>
                                        <th>Title</th>
                                        <th>Source</th>
                                        <th>URL</th>
                                        <th>Category</th>
                                        <th class="no-sort">Edit</th>

                                    </tr>
                                </thead>
                                {{-- <tbody>
                                    <?php foreach ($menu_list as $menu):?>
                                    @csrf
                                    <tr>
                                        <td>{{$menu->id}}</td>
                                        <td><strong>{{$menu->menu_name}}</strong></td>
                                        <td>{{$menu->route}}</td>
                                        <td> @if($menu->active == '1')
                                                <span class="label label-success">Active</span>
                                             @else
                                                <span class="label label-warning">Unactive</span>
                                            @endif
                                        </td>
                                        <td> @if($menu->admin_access == '1')
                                                <span class="label label-success">Accessable</span>
                                             @else
                                                <span class="label label-warning">Unactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            
                                            <a href="#myModal" data-toggle="modal">
                                                <button type="button" class="btn btn-success" data-action="expand-all">Edit</button>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach?>
                                  --}}   
                                    
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th>ID</th>
                                        <th>Photo</th>
                                        <th>Title</th>
                                        <th>Source</th>
                                        <th>URL</th>
                                        <th>Category</th>
                                        <th >Edit</th>

                                    </tr>
                                </tfoot>
                            </table>



                        </div>

                        <div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog"  id="newsModal" class="modal fade">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
                                        <h4 class="modal-title"  id="newsCrudModal">Form Tittle</h4>
                                    </div>
                                    <div class="modal-body row">
                                        <div class="col-md-5 img-modal">
                                            <img src="{{asset('img/img_null.png')}}" id="img-display" alt="">
                                            

                                        </div>

                                        <div class="col-md-7">

                                            <form role="form" id="newsForm" name="newsForm">
                                                <input type="hidden" name="id" id="id">
                                                <div class="form-group">
                                                    <label for="title">News Title</label>
                                                    <input type="text" class="form-control" id="title" name="title" placeholder="Enter News Title">
                                                </div>
                                                <div class="form-group">
                                                    <label for="photo_url">Photo URL</label>
                                                    <input type="text" class="form-control" id="photo_url" name="photo_url" placeholder="Enter Photo URL">
                                                    <a href="javascript:void(0);" onclick="checkImg()" class="btn" ><i class="fa fa-eye"></i> Check Image URL</a>
                                                </div>
                                                <div class="form-group">
                                                    <label for="source">Source</label>
                                                    <input type="text" class="form-control" id="source" name="source" placeholder="Enter Source">
                                                </div>
                                                <div class="form-group">
                                                    <label for="url">URL</label>
                                                    <input type="text" class="form-control" id="url" name="url" placeholder="Enter  URL">
                                                </div>
                                                <div class="form-group">
                                                    <label for="category">Category</label>
                                                    <select class="form-control" id="category" name="category" >
                                                        <?php foreach ($category_list as $category):?>
                                                        <option value="{{$category->id}}">{{$category->name}}</option>
                                                        <?php endforeach?>
                                                    </select>
                                                </div>
                                                <button type="submit" class="btn btn-primary" id="btn-save">Submit</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
    @section('script')
        {{-- <script>
         var SITEURL = '{{route('dashboard')}}';
         $(document).ready( function () {
           $.ajaxSetup({
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              }
          });
          $('#dynamic-table').DataTable({
                 processing: true,
                 serverSide: true,
                 ajax: {
                  url: SITEURL + "menu",
                  type: 'GET',
                 },
                 columns: [
                          { data: 'id', name: 'id'},
                          { data: 'menu_name', name: 'menu_name' },
                          { data: 'route', name: 'route' },
                          { data: 'active', name: 'active' },
                          { data: 'admin_access', name: 'admin_access' },
                          { data: 'edit', name: 'edit', orderable: false},
                       ],
                order: [[0, 'desc']]
              });
         
         /*  When user click add user button */
            // $('#create-new-product').click(function () {
            //     $('#btn-save').val("create-product");
            //     $('#product_id').val('');
            //     $('#productForm').trigger("reset");
            //     $('#productCrudModal').html("Add New Product");
            //     $('#ajax-product-modal').modal('show');
            // });
          
           /* When click edit user */
           //  $('body').on('click', '.edit-product', function () {
           //    var product_id = $(this).data('id');
           //    $.get('product-list/' + product_id +'/edit', function (data) {
           //       $('#title-error').hide();
           //       $('#product_code-error').hide();
           //       $('#description-error').hide();
           //       $('#productCrudModal').html("Edit Product");
           //        $('#btn-save').val("edit-product");
           //        $('#ajax-product-modal').modal('show');
           //        $('#product_id').val(data.id);
           //        $('#title').val(data.title);
           //        $('#product_code').val(data.product_code);
           //        $('#description').val(data.description);
           //    })
           // });
         
            // $('body').on('click', '#delete-product', function () {
          
            //     var product_id = $(this).data("id");
                
            //     if(confirm("Are You sure want to delete !")){
            //       $.ajax({
            //           type: "get",
            //           url: SITEURL + "product-list/delete/"+product_id,
            //           success: function (data) {
            //           var oTable = $('#laravel_datatable').dataTable(); 
            //           oTable.fnDraw(false);
            //           },
            //           error: function (data) {
            //               console.log('Error:', data);
            //           }
            //       });
            //     }
            // }); 
           
           });
          
        // if ($("#productForm").length > 0) {
        //       $("#productForm").validate({
          
        //      submitHandler: function(form) {
          
        //       var actionType = $('#btn-save').val();
        //       $('#btn-save').html('Sending..');
               
        //       $.ajax({
        //           data: $('#productForm').serialize(),
        //           url: SITEURL + "product-list/store",
        //           type: "POST",
        //           dataType: 'json',
        //           success: function (data) {
          
        //               $('#productForm').trigger("reset");
        //               $('#ajax-product-modal').modal('hide');
        //               $('#btn-save').html('Save Changes');
        //               var oTable = $('#laravel_datatable').dataTable();
        //               oTable.fnDraw(false);
                       
        //           },
        //           error: function (data) {
        //               console.log('Error:', data);
        //               $('#btn-save').html('Save Changes');
        //           }
        //       });
        //     }
        //   })
        // }
        </script> --}}

        <script>
            $(document).ready( function () {
                $.ajaxSetup({
                    headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $('#news_table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ url('/dashboard/news') }}",
                    columns: [
                        { data: 'id', name: 'id'},
                        { data: 'photo_url', "render": function (data, type, row) {
                            return '<img class="img-row" src="'+data+'"></span>';
                            
                        } },
                        { data: 'title', name: 'title' },
                        { data: 'source', name: 'source' },
                        { data: 'url', name: 'url' },
                        { data: 'category', "render": function (data, type, row) {
                            return data.name;
                            
                        } },
                        { data: 'action', name: 'action', orderable: false},
                    ],
                    order: [[0, 'desc']],
                });
            });
            function add(){
                $("#img-display").attr("src","{{asset('img/img_null.png')}}");
                $('#newsForm').trigger("reset");
                $('#newsCrudModal').html("Add News");
                $('#newsModal').modal('show');
                $('#id').val('');
            };
            function editFunc(id){
                $.ajax({
                    type:"POST",
                    url: "{{ url('/dashboard/news/edit') }}",
                    data: { id: id },
                    dataType: 'json',
                    success: function(res){
                        $('#newsCrudModal').html("Edit News");
                        $('#newsModal').modal('show');
                        $('#id').val(res.id);
                        $('#title').val(res.title);
                        $("#img-display").attr("src",res.photo_url);
                        $('#photo_url').val(res.photo_url);
                        $('#source').val(res.source);
                        $('#url').val(res.url);
                    },
                    error: function(data){
                        console.log(data);
                    }
                });
            }; 
            function deleteFunc(id){
                if (confirm("Delete Record?") == true) {
                    var id = id;
                    // ajax
                    $.ajax({
                        type:"POST",
                        url: "{{ url('/dashboard/news/delete') }}",
                        data: { id: id },
                        dataType: 'json',
                        success: function(res){
                            var oTable = $('#news_table').dataTable();
                            oTable.fnDraw(false);
                        },
                        error: function(data){
                            console.log(data);
                        }
                    });
                }
            }
            function checkImg(){
                var url=$("#photo_url").val();
                console.log(url);
                $("#img-display").attr("src",url);

                console.log($("#img-display").attr("src"));
            }
            $('#newsForm').submit(function(e) {
                e.preventDefault();
                var formData = new FormData(this);
                $.ajax({
                    type:'POST',
                    url: "{{ url('/dashboard/news/store')}}",
                    data: formData,
                    cache:false,
                    contentType: false,
                    processData: false,
                    success: (data) => {
                        $("#newsModal").modal('hide');
                        var oTable = $('#news_table').dataTable();
                        oTable.fnDraw(false);
                        $("#btn-save").html('Submit');
                        $("#btn-save").attr("disabled", false);
                    },
                    error: function(data){
                        console.log(data);
                    }
                });
            });

        </script>
    @endsection
        <!--body wrapper end-->
</x-app-layout>
