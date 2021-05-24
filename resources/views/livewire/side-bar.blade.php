
<div class="left-side sticky-left-side">

    <!--logo and iconic logo start-->
    <div class="logo">
        <a href="index.html"><img src="{{asset('img/logo.png')}}" alt=""></a>
    </div>

    <div class="logo-icon text-center">
        <a href="index.html"><img src="{{asset('img/logo_icon.png')}}" alt=""></a>
    </div>
    <!--logo and iconic logo end-->

    <div class="left-side-inner">

        <!-- visible to small devices only -->
        <div class="visible-xs hidden-sm hidden-md hidden-lg">
            <div class="media logged-user">
                
            </div>

            
        </div>

        <!--sidebar nav start-->
        <ul class="nav nav-pills nav-stacked custom-nav">
            <li @if (\Request::is('dashboard')) class="active" @endif><a href="{{route('dashboard')}}"><i class="fas fa-home"></i> <span>Dashboard</span></a></li>

            @if(Auth::guard('admin')->check())
                @if(Auth::user()->role=='superadmin')

                    

                @endif
                @if(Auth::user()->role=='systemadmin')

                    {{-- <li @if (\Request::is('dashboard/menu')) class="active" @endif ><a href="{{url('/dashboard/menu')}}"><i class="fas fa-filter"></i> <span>Manage Menu</span></a></li> --}}

                    <li @if (\Request::is('dashboard/admin')) class="active" @endif ><a href="{{url('/dashboard/admin')}}"><i class="fas fa-user-shield"></i> <span>Admin</span></a></li>
                    

                @endif
                <li @if (\Request::is('dashboard/user')) class="active" @endif ><a href="{{url('/dashboard/user')}}"><i class="fas fa-users"></i> <span>Users</span></a></li>

                <li class="menu-list @if (\Request::is('dashboard/notification/*')||\Request::is('dashboard/notification')) active @endif"><a href=""><i class="fas fa-bell"></i> <span>User Notification</span></a>
                    <ul class="sub-menu-list">
                        <li><a href="{{url('/dashboard/notification/category')}}"> Manage Notification Category</a></li>
                        <li><a href="{{url('/dashboard/notification')}}"> Manage Notification</a></li>
                    </ul>
                </li>

                

                <li @if (\Request::is('dashboard/company')) class="active" @endif ><a href="{{url('/dashboard/company')}}"><i class="fas fa-building"></i> <span>Manage Company</span></a></li>
                
                <li class="menu-list @if (\Request::is('dashboard/jobs/*')||\Request::is('dashboard/jobs')) active @endif"><a href=""><i class="fas fa-mail-bulk"></i> <span>Jobs</span></a>
                    <ul class="sub-menu-list">
                        <li><a href="{{url('/dashboard/jobs/category')}}"> Manage Jobs Category</a></li>
                        <li><a href="{{url('/dashboard/jobs')}}"> Manage Jobs</a></li>
                    </ul>
                </li>


                <li class="menu-list @if (\Request::is('dashboard/news/*')||\Request::is('dashboard/news')) active @endif"><a href=""><i class="fas fa-newspaper"></i> <span>News</span></a>
                    <ul class="sub-menu-list">
                        <li><a href="{{url('/dashboard/news/category')}}"> Manage News Category</a></li>
                        <li><a href="{{url('/dashboard/news')}}"> Manage News</a></li>
                    </ul>
                </li>

                <li><a href="{{url('/dashboard/')}}"><i class="fas fa-concierge-bell"></i> <span>Manage Service</span></a></li>

                <li><a href="{{url('/dashboard/')}}"><i class="fas fa-user-cog"></i> <span>Manage Service Admin</span></a></li>

                

                <li><a href="{{url('/dashboard/')}}"><i class="fas fa-cogs"></i> <span>Settings</span></a></li>
                
                <li><a href="{{url('/dashboard/')}}"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li>

            @elseif(Auth::guard('service_admin')->check())

            @endif

            {{-- <li class="menu-list"><a href=""><i class="fa fa-laptop"></i> <span>Layouts</span></a>
                <ul class="sub-menu-list">
                    <li><a href="blank_page.html"> Blank Page</a></li>
                    <li><a href="boxed_view.html"> Boxed Page</a></li>
                    <li><a href="leftmenu_collapsed_view.html"> Sidebar Collapsed</a></li>
                    <li><a href="horizontal_menu.html"> Horizontal Menu</a></li>

                </ul>
            </li>
            <li class="menu-list"><a href=""><i class="fa fa-book"></i> <span>UI Elements</span></a>
                <ul class="sub-menu-list">
                    <li><a href="general.html"> General</a></li>
                    <li><a href="buttons.html"> Buttons</a></li>
                    <li><a href="tabs-accordions.html"> Tabs & Accordions</a></li>
                    <li><a href="typography.html"> Typography</a></li>
                    <li><a href="slider.html"> Slider</a></li>
                    <li><a href="panels.html"> Panels</a></li>
                </ul>
            </li>
            <li class="menu-list"><a href=""><i class="fa fa-cogs"></i> <span>Components</span></a>
                <ul class="sub-menu-list">
                    <li><a href="grids.html"> Grids</a></li>
                    <li><a href="gallery.html"> Media Gallery</a></li>
                    <li><a href="calendar.html"> Calendar</a></li>
                    <li><a href="tree_view.html"> Tree View</a></li>
                    <li><a href="nestable.html"> Nestable</a></li>

                </ul>
            </li>

            <li><a href="fontawesome.html"><i class="fa fa-bullhorn"></i> <span>Fontawesome</span></a></li>

            <li class="menu-list"><a href=""><i class="fa fa-envelope"></i> <span>Mail</span></a>
                <ul class="sub-menu-list">
                    <li><a href="mail.html"> Inbox</a></li>
                    <li><a href="mail_compose.html"> Compose Mail</a></li>
                    <li><a href="mail_view.html"> View Mail</a></li>
                </ul>
            </li>

            <li class="menu-list"><a href=""><i class="fa fa-tasks"></i> <span>Forms</span></a>
                <ul class="sub-menu-list">
                    <li><a href="form_layouts.html"> Form Layouts</a></li>
                    <li><a href="form_advanced_components.html"> Advanced Components</a></li>
                    <li><a href="form_wizard.html"> Form Wizards</a></li>
                    <li><a href="form_validation.html"> Form Validation</a></li>
                    <li><a href="editors.html"> Editors</a></li>
                    <li><a href="inline_editors.html"> Inline Editors</a></li>
                    <li><a href="pickers.html"> Pickers</a></li>
                    <li><a href="dropzone.html"> Dropzone</a></li>
                </ul>
            </li>
            <li class="menu-list"><a href=""><i class="fa fa-bar-chart-o"></i> <span>Charts</span></a>
                <ul class="sub-menu-list">
                    <li><a href="flot_chart.html"> Flot Charts</a></li>
                    <li><a href="morris.html"> Morris Charts</a></li>
                    <li><a href="chartjs.html"> Chartjs</a></li>
                    <li><a href="c3chart.html"> C3 Charts</a></li>
                </ul>
            </li>
            <li class="menu-list"><a href="#"><i class="fa fa-th-list"></i> <span>Data Tables</span></a>
                <ul class="sub-menu-list">
                    <li><a href="basic_table.html"> Basic Table</a></li>
                    <li><a href="dynamic_table.html"> Advanced Table</a></li>
                    <li><a href="responsive_table.html"> Responsive Table</a></li>
                    <li><a href="editable_table.html"> Edit Table</a></li>
                </ul>
            </li>

            <li class="menu-list"><a href="#"><i class="fa fa-map-marker"></i> <span>Maps</span></a>
                <ul class="sub-menu-list">
                    <li><a href="google_map.html"> Google Map</a></li>
                    <li><a href="vector_map.html"> Vector Map</a></li>
                </ul>
            </li>
            <li class="menu-list"><a href=""><i class="fa fa-file-text"></i> <span>Extra Pages</span></a>
                <ul class="sub-menu-list">
                    <li><a href="profile.html"> Profile</a></li>
                    <li><a href="invoice.html"> Invoice</a></li>
                    <li><a href="pricing_table.html"> Pricing Table</a></li>
                    <li><a href="timeline.html"> Timeline</a></li>
                    <li><a href="blog_list.html"> Blog List</a></li>
                    <li><a href="blog_details.html"> Blog Details</a></li>
                    <li><a href="directory.html"> Directory </a></li>
                    <li><a href="chat.html"> Chat </a></li>
                    <li><a href="404.html"> 404 Error</a></li>
                    <li><a href="500.html"> 500 Error</a></li>
                    <li><a href="registration.html"> Registration Page</a></li>
                    <li><a href="lock_screen.html"> Lockscreen </a></li>
                </ul>
            </li>
            <li><a href="login.html"><i class="fa fa-sign-in"></i> <span>Login Page</span></a></li> --}}

        </ul>
        <!--sidebar nav end-->

    </div>
</div>
