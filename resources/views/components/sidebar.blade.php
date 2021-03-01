<aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less -->
    <section class="sidebar">
        <!-- Sidebar user panel -->
        <div class="user-panel">
            <div class="pull-left image">
                <img src="dist/img/user2-160x160.jpg" class="img-circle" alt="User Image">
            </div>
            <div class="pull-left info">
                <p>Admin</p>
                <!--<a href="#"><i class="fa fa-circle text-success"></i> </a>-->
                
                <div>
                    <div class="">
                        @if( ChatStatus::getStatus() == 0 )
                            <form method="POST" action="{{ route('change.chat.status') }}">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input type="hidden" name="status" value="1">
                                <button type="submit" class="btn btn-xs btn-primary">Enable Chat</button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('change.chat.status') }}">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input type="hidden" name="status" value="0">
                                <button type="submit" class="btn btn-xs btn-danger">Disable Chat</button>
                            </form>                        
                        @endif
                    </div> 
                </div>  
            </div>
        </div>
        <!-- search form -->
        <form action="#" method="get" class="sidebar-form">
            <div class="input-group">
                <input type="text" name="q" class="form-control" placeholder="Search...">
                <span class="input-group-btn">
                <button type="submit" name="search" id="search-btn" class="btn btn-flat"><i class="fa fa-search"></i>
                </button>
              </span>
            </div>
        </form>
        @php
            $currentRoute = request()->route()->getName();
        @endphp
        <!-- /.search form -->
        <!-- sidebar menu: : style can be found in sidebar.less -->
        <ul class="sidebar-menu" data-widget="tree">
            <li class="header">MAIN NAVIGATION</li>
            <li class="{{ ($currentRoute == 'home')?'active':'' }}">
                <a href="{{ route('home') }}">
                    <i class="fa fa-dashboard"></i> <span>Dashboard</span>
                </a>
            </li>

            @if(auth()->user()->role == 2 && auth()->user()->id == 1)

                <li class="treeview {{ (in_array($currentRoute,['users','create.user','edit.user','designations']) && in_array(request()->role, [2,3,4,5]))?'active':'' }}">
                    <a href="javascript:void(0)">
                        <i class="fa fa-users"></i> <span>User Management</span>
                        <span class="pull-right-container">
                                <i class="fa fa-angle-left pull-right"></i>
                            </span>
                    </a>
                    <ul class="treeview-menu">
                        <li class="{{ (in_array($currentRoute, ['users','create.user','edit.user']) && request()->role == 2)?'active':'' }}"><a href="{{ route('users','2') }}"><i class="fa fa-circle-o"></i> Admin</a></li>
                        <li class="{{ (in_array($currentRoute, ['users','create.user','edit.user']) && request()->role == 3)?'active':'' }}"><a href="{{ route('users','3') }}"><i class="fa fa-circle-o"></i> Employees</a></li>
                        <li class="{{ (in_array($currentRoute, ['users','create.user','edit.user']) && request()->role == 4)?'active':'' }}"><a href="{{ route('users','4') }}"><i class="fa fa-circle-o"></i> Seller</a></li>
                        <li class="{{ (in_array($currentRoute, ['users','create.user','edit.user']) && request()->role == 5)?'active':'' }}"><a href="{{ route('users','5') }}"><i class="fa fa-circle-o"></i> Buyer</a></li>
                    </ul>
                </li>
                <li class="{{ (in_array($currentRoute,['modules','permissions']))?'active':'' }}">
                    <a href="{{ route('modules') }}">
                        <i class="fa fa-users"></i> <span>Modules & Permissions</span>
                    </a>
                </li>
                <li class="{{ (in_array($currentRoute,['designations','create.designation','edit.designation']))?'active':'' }}">
                    <a href="{{ route('designations') }}">
                        <i class="fa fa-address-card-o"></i> <span>Designations</span>
                    </a>
                </li>
            @endif

            @php
                $modules = \App\Module::modulesList();
            @endphp

            @foreach($modules as $key => $module)
                @php
                    $routes = $module->permissions->map(function($item){
                        return $item->route_name;
                    });
                @endphp
                @if($module->is_treeview)
                    <li class="treeview {{ (in_array($currentRoute,$routes->toArray()))?'active':'' }}">
                        <a href="javascript:void(0)">
                            <i class="fa {{ $module->icon }}"></i> <span>{{ $module->name }}</span>
                            <span class="pull-right-container">
                                <i class="fa fa-angle-left pull-right"></i>
                            </span>
                        </a>

                        <ul class="treeview-menu">
                            @php
                                $viewPermission = $module->permissions->where('action','view');
                                $createPermission = $module->permissions->where('action','create');
                                $hasViewPermission = (!$viewPermission->isEmpty())?true:false;
                                $hasCreatePermission = (!$createPermission->isEmpty())?true:false;
                            @endphp
                            @if($hasViewPermission)
                                <li class="{{ ($currentRoute == $viewPermission->first()->route_name)?'active':'' }}"><a href="{{ route($viewPermission->first()->route_name) }}"><i class="fa fa-circle-o"></i> List Records</a></li>
                            @endif
                            @if($hasCreatePermission)
                                <li class="{{ ($currentRoute == $createPermission->first()->route_name)?'active':'' }}"><a href="{{ route($createPermission->first()->route_name) }}"><i class="fa fa-circle-o"></i> Create New</a></li>
                            @endif
                        </ul>
                    </li>
                @else
                    <li class="{{ ($currentRoute == $module->slug)?'active':'' }}">
                        <a href="{{ route($module->slug) }}">
                            <i class="fa {{ $module->icon }}"></i> <span>{{ $module->name }}</span>
                        </a>
                    </li>
                @endif
            @endforeach

            <li class="{{ (in_array($currentRoute,['gallery','gallery.create','gallery.delete']))?'active':'' }}">
                <a href="{{ route('gallery') }}">
                    <i class="fa fa-address-card-o"></i> <span>Gallery</span>
                </a>
            </li>
            <li class="{{ (in_array($currentRoute,['ports','ports.create','ports.delete']))?'active':'' }}">
                <a href="{{ route('ports') }}">
                    <i class="fa fa-address-card-o"></i> <span>Transport</span>
                </a>
            </li>
            <li class="{{ (in_array($currentRoute,['plan.create']))?'active':'' }}">
                <a href="{{ route('plan.create') }}">
                    <i class="fa fa-address-card-o"></i> <span> Plan </span>
                </a>
            </li>
            <li class="{{ (in_array($currentRoute,['send.push.notification']))?'active':'' }}">
                <a href="{{ route('send.push.notification') }}">
                    <i class="fa fa-address-card-o"></i> <span> Push Notification </span>
                </a>
            </li>
            <li class="{{ (in_array($currentRoute,['trial.period']))?'active':'' }}">
                <a href="{{ route('trial.period') }}">
                    <i class="fa fa-address-card-o"></i> <span> User App Trial </span>
                </a>
            </li>
            <li class="{{ (in_array($currentRoute,['master.index']))?'active':'' }}">
                <a href="{{ route('master.index') }}">
                    <i class="fa fa-address-card-o"></i> <span> Master </span>
                </a>
            </li>

        </ul>
    </section>
    <!-- /.sidebar -->
</aside>
