<x-app-layout>
    @section('style')
    <style type="text/css">
        .img-row{
            max-height: 80px; 
        }
    </style>
    @endsection
    <!-- page heading start-->
    <div class="page-heading">
        <h3>
            Manage Jobs
        </h3>
        <ul class="breadcrumb">
            <li>
                <a href="{{route('dashboard')}}">Dashboard</a>
            </li>
            <li class="active"> Manage Jobs</li>
        </ul>
    </div>
    <!-- page heading end-->

    <!--body wrapper start-->
    <div class="wrapper">
        <div class="row">
            <div class="col-sm-12">
                <section class="panel">
                    <header class="panel-heading">
                        Manage Jobs
                        {{-- <span class="pull-right">
                            <a href="#postModal" data-toggle="modal" onclick="add()" class=" btn btn-primary btn-sm">Add Jobs <i class="fa fa-plus"></i></a>
                            <a href="javascript:;" class="fa fa-times"></a>
                         </span> --}}
                    </header>
                    <div class="panel-body">
                        <div class="adv-table">
                            <table  class="display table  table-striped" id="job_vacancy_table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Photo</th>
                                        <th>Title</th>
                                        <th>Category</th>
                                        <th>Company</th>
                                        <th>Location</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th class="no-sort">Action</th>

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
                                        <th>Photo</th>
                                        <th>Title</th>
                                        <th>Category</th>
                                        <th>Company</th>
                                        <th>Location</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th >Action</th>

                                    </tr>
                                </tfoot>
                            </table>



                        </div>

                        {{-- <div aria-hidden="true" role="dialog"  id="postModal" class="modal fade">
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
                        </div> --}}
                        



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
                $('#job_vacancy_table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ url('/dashboard/jobs') }}",
                    columns: [

                                        
                        { data: 'id', name: 'id'},
                        { data: 'photo', "render": function (data, type, row) {
                            return '<img class="img-row" src="'+data+'"></span>';
                            
                        } },
                        { data: 'title', name: 'title'},
                        { data: 'category', name: 'category'},
                        { data: 'company.name', name: 'company.name'},
                        { data: 'city', name: 'city'},
                        { data: 'stat', name: 'stat'},
                        { data: 'date', name: 'date'},
                        { data: 'action', name: 'action', orderable: false},
                    ],
                    order: [[0, 'asc']],
                }); 
            });

            function acc(id){
                if (confirm("Accept Post?") == true) {
                    var id = id;
                    // ajax
                    $.ajax({
                        type:"POST",
                        url: "{{ url('/dashboard/jobs/accept') }}",
                        data: { id: id },
                        dataType: 'json',
                        success: function(res){
                            var oTable = $('#job_vacancy_table').dataTable();
                            oTable.fnDraw(false);
                        },
                        error: function(data){
                            console.log(data);
                        }
                    });
                }
            }
            function block(id){
                if (confirm(" Block Post?") == true) {
                    var id = id;
                    // ajax
                    $.ajax({
                        type:"POST",
                        url: "{{ url('/dashboard/jobs/block') }}",
                        data: { id: id },
                        dataType: 'json',
                        success: function(res){
                            var oTable = $('#job_vacancy_table').dataTable();
                            oTable.fnDraw(false);
                        },
                        error: function(data){
                            console.log(data);
                        }
                    });
                }
            }
            function clos(id){
                if (confirm("Close Post?") == true) {
                    var id = id;
                    // ajax
                    $.ajax({
                        type:"POST",
                        url: "{{ url('/dashboard/jobs/close') }}",
                        data: { id: id },
                        dataType: 'json',
                        success: function(res){
                            var oTable = $('#job_vacancy_table').dataTable();
                            oTable.fnDraw(false);
                        },
                        error: function(data){
                            console.log(data);
                        }
                    });
                }
            }
            
            


            

        </script>
    @endsection
        <!--body wrapper end-->
</x-app-layout>
