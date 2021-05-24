<x-app-layout>
    <!-- page heading start-->
    <div class="page-heading">
        <h3>
            Manage Menu
        </h3>
        <ul class="breadcrumb">
            <li>
                <a href="{{route('dashboard')}}">Dashboard</a>
            </li>
            <li class="active"> Manage Menu </li>
        </ul>
    </div>
    <!-- page heading end-->

    <!--body wrapper start-->
    <div class="wrapper">
        <div class="row">
            <div class="col-sm-12">
                <section class="panel">
                    <header class="panel-heading">
                        Manage Menu
                        <span class="pull-right">
                            <a href="javascript:void(0);" onclick="add()" class=" btn btn-primary btn-sm">Add menu  <i class="fa fa-plus"></i></a>
                            {{-- <a href="javascript:;" class="fa fa-times"></a> --}}
                         </span>
                    </header>
                    <div class="panel-body">
                        <div class="adv-table">
                            <table  class="display table  table-striped" id="menu_table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Menu Name</th>
                                        <th>Route</th>
                                        <th>Active</th>
                                        <th>Admin Access</th>
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
                                        <th>Menu Name</th>
                                        <th>Route</th>
                                        <th>Active</th>
                                        <th>Admin Access</th>
                                        <th>Edit</th>

                                    </tr>
                                </tfoot>
                            </table>



                        </div>

                        <div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog"  id="menuModal" class="modal fade">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
                                        <h4 class="modal-title"  id="menuCrudModal">Form Tittle</h4>
                                    </div>
                                    <div class="modal-body">

                                        <form role="form" id="menuForm" name="menuForm">
                                            <input type="hidden" name="id" id="id">
                                            <div class="form-group">
                                                <label for="menu-name">Menu Name</label>
                                                <input type="text" class="form-control" id="menu_name" name="menu_name" placeholder="Enter Menu Name">
                                            </div>
                                            <div class="form-group">
                                                <label for="route">Route</label>
                                                <input type="text" class="form-control" id="route" name="route" placeholder="Enter Route">
                                            </div>
                                            <div class="checkbox">
                                                <label>
                                                    <input type="checkbox" name="active" id="active"> Active
                                                </label>
                                                
                                            </div>
                                            <div class="checkbox">
                                                <label>
                                                    <input type="checkbox" name="admin_access" id="admin_access"> Admin Access
                                                </label>
                                                
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
                $('#menu_table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ url('/dashboard/menu') }}",
                    columns: [
                        { data: 'id', name: 'id'},
                        { data: 'menu_name', name: 'menu_name' },
                        { data: 'route', name: 'route' },
                        { data: 'active',  "render": function (data, type, row) {
                            if (data == '1') {
                                return '<span class="label label-success label-mini">Active</span>';
                            }
                            else {
                                return '<span class="label label-warning label-mini">Unactive</span>';
                            }
                        } },
                        { data: 'admin_access', "render": function (data, type, row) {
                            if (data == '1') {
                                return '<span class="label label-success label-mini">Accessable</span>';
                            }
                            else {
                                return '<span class="label label-warning label-mini">Unaccessable</span>';
                            }
                        } },
                        {data: 'action', name: 'action', orderable: false},
                    ],
                    order: [[0, 'asc']],
                });
            });
            function add(){
                $('#menuForm').trigger("reset");
                $('#menuCrudModal').html("Add Menu");
                $('#menuModal').modal('show');
                $('#id').val('');
            };
            function editFunc(id){
                $.ajax({
                    type:"POST",
                    url: "{{ url('/dashboard/menu/edit') }}",
                    data: { id: id },
                    dataType: 'json',
                    success: function(res){
                        $('#menuCrudModal').html("Edit Company");
                        $('#menuModal').modal('show');
                        $('#id').val(res.id);
                        $('#menu_name').val(res.menu_name);
                        $('#route').val(res.route);
                        if(res.active==1){
                            $('#active').prop("checked",true);
                        }else{
                            $('#active').prop("checked",false);
                        }
                        if(res.admin_access==1){
                            $('#admin_access').prop("checked",true);
                        }else{
                            $('#admin_access').prop("checked",false);
                        }
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
                        url: "{{ url('/dashboard/menu/delete') }}",
                        data: { id: id },
                        dataType: 'json',
                        success: function(res){
                            var oTable = $('#menu_table').dataTable();
                            oTable.fnDraw(false);
                        },
                        error: function(data){
                            console.log(data);
                        }
                    });
                }
            }
            $('#menuForm').submit(function(e) {
                e.preventDefault();
                var formData = new FormData(this);
                $.ajax({
                    type:'POST',
                    url: "{{ url('/dashboard/menu/store')}}",
                    data: formData,
                    cache:false,
                    contentType: false,
                    processData: false,
                    success: (data) => {
                        $("#menuModal").modal('hide');
                        var oTable = $('#menu_table').dataTable();
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
