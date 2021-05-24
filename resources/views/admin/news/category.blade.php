<x-app-layout>
    @section('style')
    <style type="text/css">
        .img-row{
            max-height: 100px; 
        }
    </style>
    @endsection
    <!-- page heading start-->
    <div class="page-heading">
        <h3>
            Manage News Category
        </h3>
        <ul class="breadcrumb">
            <li>
                <a href="{{route('dashboard')}}">Dashboard</a>
            </li>
            <li class="active"> Manage News Category</li>
        </ul>
    </div>
    <!-- page heading end-->

    <!--body wrapper start-->
    <div class="wrapper">
        <div class="row">
            <div class="col-sm-12">
                <section class="panel">
                    <header class="panel-heading">
                        Manage News Category
                        <span class="pull-right">
                            <a href="javascript:void(0);" onclick="add()" class=" btn btn-primary btn-sm">Add News Category <i class="fa fa-plus"></i></a>
                            {{-- <a href="javascript:;" class="fa fa-times"></a> --}}
                         </span>
                    </header>
                    <div class="panel-body">
                        <div class="adv-table">
                            <table  class="display table  table-striped" id="news_table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Category Name</th>
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
                                        <th>Category Name</th>
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
                                        <h4 class="modal-title"  id="newsCrudModal">Form Title</h4>
                                    </div>
                                    <div class="modal-body">
                                        <form role="form" id="newsForm" name="newsForm">
                                            <input type="hidden" name="id" id="id">
                                            <div class="form-group">
                                                <label for="name">News Category</label>
                                                <input type="text" class="form-control" id="name" name="name" placeholder="Enter News Caegory">
                                            </div>
                                            
                                            <button type="submit" class="btn btn-primary" id="btn-save">Submit</button>
                                        </form>
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
                    ajax: "{{ url('/dashboard/news/category') }}",
                    columns: [
                        { data: 'id', name: 'id'},
                        { data: 'name', name: 'name'},
                        { data: 'action', name: 'action', orderable: false},
                    ],
                    order: [[0, 'asc']],
                });
            });
            function add(){
                $('#newsForm').trigger("reset");
                $('#newsCrudModal').html("Add News Category");
                $('#newsModal').modal('show');
                $('#id').val('');
            };
            function editFunc(id){
                $.ajax({
                    type:"POST",
                    url: "{{ url('/dashboard/news/category/edit') }}",
                    data: { id: id },
                    dataType: 'json',
                    success: function(res){
                        $('#newsCrudModal').html("Edit News Category");
                        $('#newsModal').modal('show');
                        $('#id').val(res.id);
                        $('#name').val(res.name);
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
                        url: "{{ url('/dashboard/news/category/delete') }}",
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
            $('#newsForm').submit(function(e) {
                e.preventDefault();
                var formData = new FormData(this);
                $.ajax({
                    type:'POST',
                    url: "{{ url('/dashboard/news/category/store')}}",
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
