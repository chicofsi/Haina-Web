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
            Manage Post Category
        </h3>
        <ul class="breadcrumb">
            <li>
                <a href="{{route('dashboard')}}">Dashboard</a>
            </li>
            <li class="active"> Manage Post Category</li>
        </ul>
    </div>
    <!-- page heading end-->

    <!--body wrapper start-->
    <div class="wrapper">
        <div class="row">
            <div class="col-sm-12">
                <section class="panel">
                    <header class="panel-heading">
                        Manage Post Category
                        <span class="pull-right">
                            <a href="#postModal" data-toggle="modal" onclick="add()" class=" btn btn-primary btn-sm">Add Post Category <i class="fa fa-plus"></i></a>
                            {{-- <a href="javascript:;" class="fa fa-times"></a> --}}
                         </span>
                    </header>
                    <div class="panel-body">
                        <div class="adv-table">
                            <table  class="display table  table-striped" id="post_table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Category Name</th>
                                        <th>Sub Category</th>
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
                                        <th>Category Name</th>
                                        <th>Sub Category</th>
                                        <th >Edit</th>

                                    </tr>
                                </tfoot>
                            </table>



                        </div>

                        <div aria-hidden="true" role="dialog"  id="postModal" class="modal fade">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
                                        <h4 class="modal-title"  id="postCrudModal">Form Title</h4>
                                    </div>
                                    <div class="modal-body">
                                        <form role="form" id="postForm" name="postForm">
                                            <input type="hidden" name="id" id="id">
                                            <div class="form-group">
                                                <label for="name">Post Category</label>
                                                <input type="text" class="form-control" id="name" name="name" placeholder="Enter Post Category">
                                            </div>
                                             <a href="#subModal" data-toggle="modal" onclick="addsub()" class=" btn btn-primary btn-sm">Add Post Sub Category <i class="fa fa-plus"></i></a>
                                            <div class="adv-table" id="sub_tablee" style="display: none ">
                                                <table  class="display table  table-striped" id="sub_table">
                                                    <thead>
                                                        <tr>
                                                            <th>ID</th>
                                                            <th>Sub Category Name</th>
                                                            <th class="no-sort">Edit</th>

                                                        </tr>
                                                    </thead>
                                                        
                                                        
                                                    <tfoot>
                                                        <tr>
                                                            <th>ID</th>
                                                            <th>Sub Category Name</th>
                                                            <th >Edit</th>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                            
                                            <button type="submit" class="btn btn-primary" id="btn-save">Submit</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog"  id="subModal" class="modal fade">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
                                        <h4 class="modal-title"  id="subCrudModal">Form Title</h4>
                                    </div>
                                    <div class="modal-body">
                                        <form role="form" id="subForm" name="subForm">
                                            <input type="hidden" name="id_category" id="id_category">
                                            <input type="hidden" name="id_sub" id="id_sub">
                                            <div class="form-group">
                                                <label for="name">Post Sub Category</label>
                                                <input type="text" class="form-control" id="name_sub" name="name_sub" placeholder="Enter Post Category">
                                            </div>
                                            
                                            <button type="submit" class="btn btn-primary" id="btn-save-sub">Submit</button>
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
                $('#post_table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ url('/dashboard/post/category') }}",
                    columns: [
                        { data: 'id', name: 'id'},
                        { data: 'name', name: 'name'},
                        { data: 'sub', name: 'sub'},
                        { data: 'action', name: 'action', orderable: false},
                    ],
                    order: [[0, 'asc']],
                });
                $('#sub_table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        "type":"POST",
                        "url": "{{ url('/dashboard/post/category/subcategory') }}",
                        "data":{
                            "id_category":"1"
                        }
                    },
                    columns: [
                        { data: 'id', name: 'id'},
                        { data: 'name', name: 'name'},
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
                $('#postForm').trigger("reset");
                $('#postCrudModal').html("Add Post Category");
                $('#sub_tablee').css('display','none');
                $('#id').val('');
            };
            
            function editFunc(id){
                $.ajax({
                    type:"POST",
                    url: "{{ url('/dashboard/post/category/edit') }}",
                    data: { id: id },
                    dataType: 'json',
                    success: function(res){
                        $('#postCrudModal').html("Edit Post Category");
                        $('#postModal').modal('show');
                        $('#id').val(res.id);
                        $('#name').val(res.name);
                        $('#sub_tablee').css('display','inline');
                        var table=$('#sub_table').DataTable();
                        table.destroy();
                        $('#sub_table').DataTable({
                            processing: true,
                            serverSide: true,
                            ajax: {
                                "type":"POST",
                                "url": "{{ url('/dashboard/post/category/subcategory') }}",
                                "data":{
                                    "id_category":res.id
                                }
                            },
                            columns: [
                                { data: 'id', name: 'id'},
                                { data: 'name', name: 'name'},
                                { data: 'action', name: 'action', orderable: false},
                            ],
                            order: [[0, 'asc']],
                        });
                        
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
                        url: "{{ url('/dashboard/post/category/delete') }}",
                        data: { id: id },
                        dataType: 'json',
                        success: function(res){
                            var oTable = $('#post_table').dataTable();
                            oTable.fnDraw(false);
                        },
                        error: function(data){
                            console.log(data);
                        }
                    });
                }
            }
            $('#postForm').submit(function(e) {
                e.preventDefault();
                var formData = new FormData(this);
                $.ajax({
                    type:'POST',
                    url: "{{ url('/dashboard/post/category/store')}}",
                    data: formData,
                    cache:false,
                    contentType: false,
                    processData: false,
                    success: (data) => {
                        $("#postModal").modal('hide');
                        var oTable = $('#post_table').dataTable();
                        oTable.fnDraw(false);
                        $("#btn-save").html('Submit');
                        $("#btn-save").attr("disabled", false);
                    },
                    error: function(data){
                        console.log(data);
                    }
                });
            });


            function addsub(){
                $('#subForm').trigger("reset");
                $('#subCrudModal').html("Add Post Sub Category"); 
                $('#id_category').val($('#id').val());
                $('#id_sub').val('');
            };
            function editsub(id){
                $.ajax({
                    type:"POST",
                    url: "{{ url('/dashboard/post/category/subcategory/edit') }}",
                    data: { id: id },
                    dataType: 'json',
                    success: function(res){
                        $('#subCrudModal').html("Edit Post Sub Category");
                        $('#id_category').val(res.id_category);
                        $('#id_sub').val(res.id);
                        $('#name_sub').val(res.name);
                    },
                    error: function(data){
                        console.log(data);
                    }
                });
            }; 
            function deletesub(id){
                if (confirm("Delete Record?") == true) {
                    var id = id;
                    // ajax
                    $.ajax({
                        type:"POST",
                        url: "{{ url('/dashboard/post/category/subcategory/delete') }}",
                        data: { id: id },
                        dataType: 'json',
                        success: function(res){
                            var oTable = $('#sub_table').dataTable();
                            oTable.fnDraw(false);
                            var hTable = $('#post_table').dataTable();
                            hTable.fnDraw(false);
                        },
                        error: function(data){
                            console.log(data);
                        }
                    });
                }
            }
            $('#subForm').submit(function(e) {
                e.preventDefault();
                var formData = new FormData(this);
                $.ajax({
                    type:'POST',
                    url: "{{ url('/dashboard/post/category/subcategory/store')}}",
                    data: formData,
                    cache:false,
                    contentType: false,
                    processData: false,
                    success: (data) => {
                        $("#subModal").modal('hide');
                        var oTable = $('#sub_table').dataTable();
                        oTable.fnDraw(false);
                        var hTable = $('#post_table').dataTable();
                        hTable.fnDraw(false);
                        $("#btn-save-sub").html('Submit');
                        $("#btn-save-sub").attr("disabled", false);
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
