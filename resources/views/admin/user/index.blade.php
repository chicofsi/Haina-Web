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
            Manage User
        </h3>
        <ul class="breadcrumb">
            <li>
                <a href="{{route('dashboard')}}">Dashboard</a>
            </li>
            <li class="active"> Manage User</li>
        </ul>
    </div>
    <!-- page heading end-->

    <!--body wrapper start-->
    <div class="wrapper">
        <div class="row">
            <div class="col-sm-12">
                <section class="panel">
                    <header class="panel-heading">
                        Manage User
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
                                        <th>Photo</th>
                                        <th>Name</th>
                                        <th >Username</th>
                                        <th>Email</th>
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
                                        <th>Name</th>
                                        <th>Username</th>
                                        <th>Email</th>
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
                                            <div class="col-md-4">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="panel">
                                                            <div class="panel-body">
                                                                <div class="profile-pic text-center">
                                                                    <img style="object-fit: contain;" id="profilePic"/>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="panel">
                                                            <div class="panel-body">
                                                                <ul class="p-info">
                                                                    <li>
                                                                        <div class="title">Gender</div>
                                                                        <div class="desk" id="profileGender">Male</div>
                                                                    </li>
                                                                    <li>
                                                                        <div class="title">Birth Date</div>
                                                                        <div class="desk" id="profileBirthdate"></div>
                                                                    </li>
                                                                    <li>
                                                                        <div class="title">Address</div>
                                                                        <div class="desk" id="profileAddress"></div>
                                                                    </li>
                                                                    <li>
                                                                        <div class="title">Phone</div>
                                                                        <div class="desk" id="profilePhonenumber"></div>
                                                                    </li>
                                                                    <li>
                                                                        <div class="title">Email</div>
                                                                        <div class="desk" id="profileEmail"></div>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="panel">
                                                            <div class="panel-body p-states">
                                                                <div class="summary pull-left">
                                                                    <h4>Total</h4>
                                                                    <span>Resume</span>
                                                                    <h3><div id="profileResumecount"></div></h3>
                                                                </div>
                                                                
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="panel">
                                                            <div class="panel-body p-states">
                                                                <div class="summary pull-left">
                                                                    <h4>Total</h4>
                                                                    <span>Portfolio</span>
                                                                    <h3><div id="profilePortfoliocount"></div></h3>
                                                                </div>
                                                                
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="panel">
                                                            <div class="panel-body p-states">
                                                                <div class="summary pull-left">
                                                                    <h4>Total</h4>
                                                                    <span>Certificate</span>
                                                                    <h3><div id="profileCertificatecount"></div></h3>
                                                                </div>
                                                                
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="panel">
                                                            <div class="panel-body">
                                                                <div class="profile-desk">
                                                                    <h1 id="profileName"></h1>
                                                                    <span id="profileUsername" style="margin-bottom: 10px"></span>
                                                                    <p id="profileAbout">
                                                                        
                                                                    </p>
                                                                    {{-- <a class="btn p-follow-btn pull-left" href="#"> <i class="fa fa-check"></i> Following</a> --}}

                                                                    <ul class="p-social-link pull-right">
                                                                        <li>
                                                                            <a href="#">
                                                                                <i class="fa fa-facebook"></i>
                                                                            </a>
                                                                        </li>
                                                                        <li class="active">
                                                                            <a href="#">
                                                                                <i class="fa fa-twitter"></i>
                                                                            </a>
                                                                        </li>
                                                                        <li>
                                                                            <a href="#">
                                                                                <i class="fa fa-google-plus"></i>
                                                                            </a>
                                                                        </li>
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                {{-- <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="panel">
                                                            <form>
                                                                <textarea class="form-control input-lg p-text-area" rows="2" placeholder="Whats in your mind today?"></textarea>
                                                            </form>
                                                            <footer class="panel-footer">
                                                                <button class="btn btn-post pull-right">Post</button>
                                                                <ul class="nav nav-pills p-option">
                                                                    <li>
                                                                        <a href="#"><i class="fa fa-user"></i></a>
                                                                    </li>
                                                                    <li>
                                                                        <a href="#"><i class="fa fa-camera"></i></a>
                                                                    </li>
                                                                    <li>
                                                                        <a href="#"><i class="fa  fa-location-arrow"></i></a>
                                                                    </li>
                                                                    <li>
                                                                        <a href="#"><i class="fa fa-meh-o"></i></a>
                                                                    </li>
                                                                </ul>
                                                            </footer>
                                                        </div>
                                                    </div>
                                                </div> --}}
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="panel">
                                                            <header class="panel-heading">
                                                                recent activities
                                                                {{-- <span class="tools pull-right">
                                                                    <a class="fa fa-chevron-down" href="javascript:;"></a>
                                                                    <a class="fa fa-times" href="javascript:;"></a>
                                                                 </span> --}}
                                                            </header>
                                                            <div class="panel-body">
                                                                <ul class="activity-list" id="profileLogs">
                                                                    {{-- <li>
                                                                        <div class="avatar">
                                                                            <img src="images/photos/user1.png" alt=""/>
                                                                        </div>
                                                                        <div class="activity-desk">
                                                                            <h5><a href="#">Jonathan Smith</a> <span>Uploaded 5 new photos</span></h5>
                                                                            <p class="text-muted" >7 minutes ago</p>
                                                                            
                                                                        </div>
                                                                    </li>
                                                                    <li>
                                                                        <div class="avatar">
                                                                            <img src="images/photos/user2.png" alt=""/>
                                                                        </div>
                                                                        <div class="activity-desk">
                                                                            <h5><a href="#">John Doe</a> <span>Completed the Sight visit.</span></h5>
                                                                            <p class="text-muted">2 minutes ago near Alaska, USA</p>
                                                                            <div class="location-map">
                                                                                <div id="map-canvas"></div>
                                                                            </div>
                                                                        </div>
                                                                    </li>

                                                                    <li>
                                                                        <div class="avatar">
                                                                            <img src="images/photos/user3.png" alt=""/>
                                                                        </div>
                                                                        <div class="activity-desk">

                                                                            <h5><a href="#">Jonathan Smith</a> <span>attended a meeting with</span>
                                                                                <a href="#">John Doe.</a></h5>
                                                                            <p class="text-muted">2 days ago near Alaska, USA</p>
                                                                        </div>
                                                                    </li>

                                                                    <li>
                                                                        <div class="avatar">
                                                                            <img src="images/photos/user4.png" alt=""/>
                                                                        </div>
                                                                        <div class="activity-desk">

                                                                            <h5><a href="#">Jonathan Smith</a> <span>completed the task “wireframe design” within the dead line</span></h5>
                                                                            <p class="text-muted">4 days ago near Alaska, USA</p>
                                                                        </div>
                                                                    </li>

                                                                    <li>
                                                                        <div class="avatar">
                                                                            <img src="images/photos/user5.png" alt=""/>
                                                                        </div>
                                                                        <div class="activity-desk">

                                                                            <h5><a href="#">Jonathan Smith</a> <span>was absent office due to sickness</span></h5>
                                                                            <p class="text-muted">4 days ago near Alaska, USA</p>
                                                                        </div>
                                                                    </li> --}}


                                                                </ul>
                                                            </div>
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
                    ajax: "{{ url('/dashboard/user') }}",
                    columns: [
                        { data: 'id', name: 'id'},
                        { data: 'photo', "render": function (data, type, row) {
                            return '<img class="img-row" src="'+data+'"></span>';
                            
                        } },
                        { data: 'fullname', name: 'fullname'},
                        { data: 'username', name: 'username'}, 
                        { data: 'email', name: 'email'},
                        { data: 'action', name: 'action', orderable: false},
                    ],
                    order: [[0, 'asc']],
                }); 
            });


            function detail(id){
                $.ajax({
                    type:"POST",
                    url: "{{ url('/dashboard/user/detail') }}",
                    data: { id: id },
                    dataType: 'json',
                    success: function(res){
                        $('#detailModalTitle').html("User Detail");
                        $('#detailModal').modal('show');

                        $('#profilePic').attr('src',res.photo_url);
                        $('#profileName').html(res.fullname);
                        $('#profileUsername').html('@'+res.username);
                        $('#profileAbout').html(res.about);
                        $('#profileGender').html(res.gender);
                        $('#profileEmail').html(res.email);
                        $('#profilePhonenumber').html(res.phone);
                        $('#profileBirthdate').html(res.birthdate);
                        $('#profileAddress').html(res.address);
                        $('#profileLogs').html(res.activity);
                        $('#profileResumecount').html(res.resumecount+" Documents");
                        $('#profilePortfoliocount').html(res.portfoliocount+" Documents");
                        $('#profileCertificatecount').html(res.certificatecount+" Documents");

                        // $("#address_list").html('');

                        // $("#photo_list").html('');
                        

                        // jQuery.each( res.address, function( i, val ) {
                        //     var add="<li><div class='well'><address><strong>"+val.address+"</strong><br>"+val.city+"</address></div></li>";
                        //     $( "#address_list" ).append(add);
                        // });

                        // jQuery.each( res.photo, function( i, val ) {
                        //     var add="<div class='images item ' ><img src="+val.photo_url+" alt= /><p>"+val.name+"</p></div>";
                        //     $( "#photo_list" ).append(add);
                        // });

                        



                        // $('#acc_button').attr("onclick","acc("+res.id+")");
                        // $('#sus_button').attr("onclick","suspend("+res.id+")");


                        
                        
                    },
                    error: function(data){
                        console.log(data);
                    }
                });

                
            }

            // function acc(id){
            //     if (confirm("Accept Company?") == true) {
            //         var id = id;
            //         // ajax
            //         $.ajax({
            //             type:"POST",
            //             url: "{{ url('/dashboard/company/accept') }}",
            //             data: { id: id },
            //             dataType: 'json',
            //             success: function(res){
            //                 $("#detailModal").modal('hide');
            //                 var oTable = $('#company_table').dataTable();
            //                 oTable.fnDraw(false);
            //             },
            //             error: function(data){
            //                 console.log(data);
            //             }
            //         });
            //     }
            // }
            // function suspend(id){
            //     if (confirm("Suspend Company?") == true) {
            //         var id = id;
            //         // ajax
            //         $.ajax({
            //             type:"POST",
            //             url: "{{ url('/dashboard/company/suspend') }}",
            //             data: { id: id },
            //             dataType: 'json',
            //             success: function(res){
            //                 $("#detailModal").modal('hide');
            //                 var oTable = $('#company_table').dataTable();
            //                 oTable.fnDraw(false);
            //             },
            //             error: function(data){
            //                 console.log(data);
            //             }
            //         });
            //     }
            // }
            
            


            

        </script>
    @endsection
        <!--body wrapper end-->
</x-app-layout>
