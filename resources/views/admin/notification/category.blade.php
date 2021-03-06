<x-app-layout>
    @section('style')
    <style type="text/css">
        .img-row{
            max-height: 50px; 
        }
    </style>
    <link rel="stylesheet" type="text/css" href="{{asset('css/bootstrap-fileupload.min.css')}}" />
    @endsection
    <!-- page heading start-->
    <div class="page-heading">
        <h3>
            Manage Notification Category
        </h3>
        <ul class="breadcrumb">
            <li>
                <a href="{{route('dashboard')}}">Dashboard</a>
            </li>
            <li class="active"> Manage Notification Category</li>
        </ul>
    </div>
    <!-- page heading end-->

    <!--body wrapper start-->
    <div class="wrapper">
        <div class="row">
            <div class="col-sm-12">
                <section class="panel">
                    <header class="panel-heading">
                        Manage Notification Category
                        <span class="pull-right">
                            <a href="#notificationCategoryModal" data-toggle="modal" onclick="add()" class=" btn btn-primary btn-sm">Add Notification Category <i class="fa fa-plus"></i></a>
                            {{-- <a href="javascript:;" class="fa fa-times"></a> --}}
                         </span>
                    </header>
                    <div class="panel-body">
                        <div class="adv-table">
                            <table  class="display table  table-striped" id="notification_category_table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th class="no-sort">Icon</th>
                                        <th>Category Name</th>
                                        <th>Notification For</th>
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
                                    
                                <tfoot>
                                    <tr>
                                        <th>ID</th>
                                        <th>Icon</th>
                                        <th>Category Name</th>
                                        <th>Notification For</th>
                                        <th >Edit</th>

                                    </tr>
                                </tfoot>
                            </table>



                        </div>

                        <div aria-hidden="true" role="dialog"  id="notificationCategoryModal" class="modal fade">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button aria-hidden="true" data-dismiss="modal" class="close" type="button">??</button>
                                        <h4 class="modal-title"  id="notificationCategoryCrudModal">Form Title</h4>
                                    </div>
                                    <div class="modal-body">
                                        <form role="form" id="notificationCategoryForm" name="notificationCategoryForm">
                                            <input type="hidden" name="id" id="id">
                                            <input type="hidden" name="type" id="type">
                                            <div class="form-group">
                                                <label for="name">Notification Category Name</label>
                                                <input type="text" class="form-control" id="name" name="name" placeholder="Enter Notification Category Name">
                                            </div>
                                            <div class="form-group">
                                                <label for="name">Notification For</label>

                                                <select class="form-control" id="notification_for" name="notification_for" >
                                                    <option value="user">User</option>
                                                    <option value="company">Company</option>
                                                </select>
                                            </div>
                                            <div class="form-group" id="icon_current">
                                                <label class="control-label">Current Icon</label>
                                                <div class="thumbnail" style="max-width: 200px; max-height: 150px; line-height: 20px;margin-bottom: 0px">
                                                    <img id="icon_cur" src="http://www.placehold.it/200x150/EFEFEF/AAAAAA&amp;text=no+image" style="max-height: 148px" alt="" />
                                                </div>
                                                <br/>
                                            </div>
                                            <div class="form-group">
                                                <label class="control-label">Notification Category Icon</label>
                                                <div class="fileupload fileupload-new" data-provides="fileupload">
                                                    <div class="fileupload-new thumbnail" style="width: 200px; height: 150px;">
                                                        <img src="http://www.placehold.it/200x150/EFEFEF/AAAAAA&amp;text=no+image" style="max-height: 150px" alt="" />
                                                    </div>
                                                    <div class="fileupload-preview fileupload-exists thumbnail" style="max-width: 200px; max-height: 150px; line-height: 20px;"></div>
                                                    <div style="margin-left: 5px">
                                                           <span class="btn btn-default btn-file">
                                                           <span class="fileupload-new"><i class="fas fa-paperclip"></i> Select image</span>
                                                           <span class="fileupload-exists"><i class="fas fa-undo"></i> Change</span>
                                                           <input id="icon" name="icon" type="file" class="default" />
                                                           </span>
                                                        <a href="#" class="btn btn-danger fileupload-exists" data-dismiss="fileupload"><i class="fas fa-trash"></i> Remove</a>
                                                    </div>
                                                </div>
                                                <br/>
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

        <script>
            $(document).ready( function () {
                $.ajaxSetup({
                    headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $('#notification_category_table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ url('/dashboard/notification/category') }}",
                    columns: [
                        { data: 'id', name: 'id'},
                        { data: 'icon', "render": function (data, type, row) {
                            return '<img class="img-row" src="'+data+'"></span>';
                        } },
                        { data: 'name', name: 'name'},
                        { data: 'for', name: 'for'},
                        { data: 'action', name: 'action', orderable: false},
                    ],
                    order: [[0, 'asc']],
                });
                

                $(document).on('show.bs.modal', '.modal', function (event) {
                    var zIndex = 1040 + (10 * $('.modal:visible').length);
                    $(this).css('z-index', zIndex);
                    setTimeout(function() {
                        $('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack');
                    }, 0);
                });
                
            });
            function add(){
                $('#notificationCategoryForm').trigger("reset");
                $('#notificationCategoryCrudModal').html("Add Notification Category");
                $('#id').val('');
                $('#icon_current').css('display','none');
                $('#type').val('add');
            };
            
            function editFunc(id){
                $.ajax({
                    type:"POST",
                    url: "{{ url('/dashboard/notification/category/edit') }}",
                    data: { id: id },
                    dataType: 'json',
                    success: function(res){
                        $('#notificationCategoryForm').trigger("reset");
                        $('#notificationCategoryCrudModal').html("Edit Notification Category");
                        $('#notificationCategoryModal').modal('show');
                        $('#id').val(res.id);
                        $('#icon_current').css('display','inline');
                        $('#type').val('edit');
                        $('#name').val(res.name);
                        $('#notification_for').val(res.notification_for);
                        $('#icon_cur').attr('src',res.img);
                        if(res.notification_for=='company'){
                            $('select option:contains("Company")').prop('selected',true);

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
                        url: "{{ url('/dashboard/notification/category/delete') }}",
                        data: { id: id },
                        dataType: 'json',
                        success: function(res){
                            var oTable = $('#notification_category_table').dataTable();
                            oTable.fnDraw(false);
                        },
                        error: function(data){
                            console.log(data);
                        }
                    });
                }
            }
            $('#notificationCategoryForm').submit(function(e) {
                e.preventDefault();
                var formData = new FormData(this);
                var changePhoto = 1;
                if ($('#icon').get(0).files.length === 0) {
                    changePhoto = 0;
                }
                formData.append("changePhoto",changePhoto);
                $.ajax({
                    type:'POST',
                    url: "{{ url('/dashboard/notification/category/store')}}",
                    data: formData,
                    cache:false,
                    contentType: false,
                    processData: false,
                    success: (data) => {
                        $("#notificationCategoryModal").modal('hide');
                        var oTable = $('#notification_category_table').dataTable();
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

        <script type="text/javascript" src="{{asset('js/bootstrap-inputmask/bootstrap-inputmask.min.js')}}"></script>
        <script type="text/javascript" src="{{asset('js/bootstrap-fileupload.min.js')}}"></script>
    @endsection
        <!--body wrapper end-->
</x-app-layout>
