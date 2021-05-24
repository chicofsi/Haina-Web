<x-app-layout>
    @section('style')
    <style type="text/css">
        .img-row{
            max-height: 60px; 
        }
        .well{
            margin-bottom: 0px !important;
        }
        ul.activity-list li{
            margin-bottom: 20px !important;
        }
    </style>
    @endsection
    <!-- page heading start-->
    <div class="page-heading">
        <h3>
            Manage Company
        </h3>
        <ul class="breadcrumb">
            <li>
                <a href="{{route('dashboard')}}">Dashboard</a>
            </li>
            <li class="active"> Manage Company</li>
        </ul>
    </div>
    <!-- page heading end-->

    <!--body wrapper start-->
    <div class="wrapper">
        <div class="row">
            <div class="col-sm-12">
                <section class="panel">
                    <header class="panel-heading">
                        Manage Company
                        <span class="pull-right">
                            {{-- <a href="#postModal" data-toggle="modal" onclick="add()" class=" btn btn-primary btn-sm">Add Jobs <i class="fa fa-plus"></i></a> --}}
                            {{-- <a href="javascript:;" class="fa fa-times"></a> --}}
                         </span>
                    </header>
                    <div class="panel-body">
                        <div class="adv-table">
                            <table  class="display table  table-striped" id="company_table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Icon</th>
                                        <th>Name</th>
                                        <th class="no-sort">User</th>
                                        <th>Status</th>
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
                                        <th>Icon</th>
                                        <th>Name</th>
                                        <th>User</th>
                                        <th>Status</th>
                                        <th >Action</th>

                                    </tr>
                                </tfoot>
                            </table>



                        </div>

                        <div aria-hidden="true" role="dialog"  id="detailModal" class="modal fade">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
                                        <h4 class="modal-title"  id="detailModalTitle">Form Title</h4>
                                    </div>
                                    <div class="modal-body" style="background-color: #eff0f4">





                                        <div class="row">
                                            <div class="col-md-12">

                                                <div class="panel">
                                                    <div class="panel-body">
                                                        <div class="col-md-4">
                                                            <div class="profile-pic text-center">
                                                                <img style="object-fit: contain;" id="companyIcon"/>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-8">
                                                            <div class="profile-desk">
                                                                <h1 id="companyName"></h1>
                                                                <p style="padding-top: 20px" id="companyDescription">
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <div class="panel">
                                                    <header class="panel-heading">
                                                        Company Address
                                                        
                                                    </header>
                                                    <div class="panel-body">
                                                        <ul class="activity-list" id="address_list">
                                                            

                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="panel">
                                                    <header class="panel-heading">
                                                        Company Photo
                                                        
                                                    </header>
                                                    <div class="panel-body">
                                                        <div id="photo_list" class="media-gal">
                                                            
                                                            

                                                            

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" id="acc_button" class="btn btn-success">Approve Request</button>
                                        <button type="button" id="sus_button" class="btn btn-danger">Suspend Company</button>
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
                $('#company_table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: "{{ url('/dashboard/company') }}",
                    columns: [
                        { data: 'id', name: 'id'},
                        { data: 'photo', "render": function (data, type, row) {
                            return '<img class="img-row" src="'+data+'"></span>';
                            
                        } },
                        { data: 'name', name: 'name'},
                        { data: 'user.username', name: 'user'}, 
                        { data: 'stat', name: 'stat'},
                        { data: 'action', name: 'action', orderable: false},
                    ],
                    order: [[0, 'asc']],
                }); 
            });


            function detail(id){
                $.ajax({
                    type:"POST",
                    url: "{{ url('/dashboard/company/detail') }}",
                    data: { id: id },
                    dataType: 'json',
                    success: function(res){
                        $('#detailModalTitle').html("Company Detail");
                        $('#detailModal').modal('show');

                        $('#companyIcon').attr('src',res.photo_url);
                        $('#companyName').html(res.name);
                        $('#companyDescription').html(res.description);

                        $("#address_list").html('');

                        $("#photo_list").html('');
                        

                        jQuery.each( res.address, function( i, val ) {
                            var add="<li><div class='well'><address><strong>"+val.address+"</strong><br>"+val.city+"</address></div></li>";
                            $( "#address_list" ).append(add);
                        });

                        jQuery.each( res.photo, function( i, val ) {
                            var add="<div class='images item ' ><img src="+val.photo_url+" alt= /><p>"+val.name+"</p></div>";
                            $( "#photo_list" ).append(add);
                        });

                        



                        $('#acc_button').attr("onclick","acc("+res.id+")");
                        $('#sus_button').attr("onclick","suspend("+res.id+")");


                        
                        
                    },
                    error: function(data){
                        console.log(data);
                    }
                });

                
            }

            function acc(id){
                if (confirm("Accept Company?") == true) {
                    var id = id;
                    // ajax
                    $.ajax({
                        type:"POST",
                        url: "{{ url('/dashboard/company/accept') }}",
                        data: { id: id },
                        dataType: 'json',
                        success: function(res){
                            $("#detailModal").modal('hide');
                            var oTable = $('#company_table').dataTable();
                            oTable.fnDraw(false);
                        },
                        error: function(data){
                            console.log(data);
                        }
                    });
                }
            }
            function suspend(id){
                if (confirm("Suspend Company?") == true) {
                    var id = id;
                    // ajax
                    $.ajax({
                        type:"POST",
                        url: "{{ url('/dashboard/company/suspend') }}",
                        data: { id: id },
                        dataType: 'json',
                        success: function(res){
                            $("#detailModal").modal('hide');
                            var oTable = $('#company_table').dataTable();
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
