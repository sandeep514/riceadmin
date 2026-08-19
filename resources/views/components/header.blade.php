<header class="main-header">
    <!-- Logo -->
    <a href="javascript:void(0)" class="logo">
        <!-- mini logo for sidebar mini 50x50 pixels -->
        <span class="logo-mini"><b>A</b>LT</span>
        <!-- logo for regular state and mobile devices -->
        <span class="logo-lg"><b>Rice</b> Admin</span>
    </a>
    <!-- Header Navbar: style can be found in header.less -->
    <nav class="navbar navbar-static-top">
        <!-- Sidebar toggle button-->
        <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
            <span class="sr-only">Toggle navigation</span>
        </a>
        @if(!empty($backupOverdue))
            <div class="backup-overdue-banner">
                <i class="fa fa-exclamation-triangle"></i>
                <strong>Database backup overdue.</strong>
                @if(!empty($lastBackupAt))
                    Last backup was {{ (int) $lastBackupDaysAgo }} day{{ (int) $lastBackupDaysAgo === 1 ? '' : 's' }} ago
                    ({{ $lastBackupAt->format('d M Y H:i') }}).
                @else
                    No backup has been downloaded yet.
                @endif
                @if(auth()->check() && (int) auth()->user()->role === 2)
                    <a href="{{ route('database.backup.download') }}">Download now</a>
                @else
                    Please download a backup from the dashboard.
                @endif
            </div>
        @endif

        <div class="navbar-custom-menu">
            <ul class="nav navbar-nav">
                <!-- User Account: style can be found in dropdown.less -->
                <li class="dropdown user user-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <img src="dist/img/user2-160x160.jpg" class="user-image" alt="User Image">
                        <span class="hidden-xs">Admin</span>
                    </a>
                    <ul class="dropdown-menu">
                        <!-- User image -->
                        <li class="user-header">
                            <img src="dist/img/user2-160x160.jpg" class="img-circle" alt="User Image">

                            <p>
                                Admin - Invoice Manager
                                <small>Member since Nov. 2012</small>
                            </p>
                        </li>
                        <!-- Menu Footer-->
                        <li class="user-footer">
                            <div class="pull-left">
                                <a href="#" class="btn btn-default btn-flat">Profile</a>
                            </div>
                            <div class="pull-right">
                                <a href="{{ route('logout') }}" class="btn btn-default btn-flat">Sign out</a>
                            </div>
                        </li>
                    </ul>
                </li>
                <!-- Control Sidebar Toggle Button -->
                <li>
                    <a href="#" data-toggle="control-sidebar"><i class="fa fa-gears"></i></a>
                </li>
            </ul>
        </div>
    </nav>
</header>
<style>
    .backup-overdue-banner {
        display: inline-block;
        vertical-align: middle;
        margin: 8px 12px;
        padding: 7px 14px;
        max-width: calc(100% - 280px);
        background: #f39c12;
        color: #111;
        font-size: 13px;
        font-weight: 600;
        border-radius: 3px;
        box-shadow: 0 0 0 2px rgba(243, 156, 18, 0.35);
        animation: backup-overdue-pulse 1.6s ease-in-out infinite;
    }
    .backup-overdue-banner a {
        color: #111;
        text-decoration: underline;
        margin-left: 6px;
        font-weight: 700;
    }
    .backup-overdue-banner i {
        margin-right: 6px;
    }
    @keyframes backup-overdue-pulse {
        0%, 100% { background: #f39c12; }
        50% { background: #ffb84d; }
    }
    @media (max-width: 767px) {
        .backup-overdue-banner {
            display: block;
            max-width: none;
            margin: 0 10px 8px;
            white-space: normal;
        }
    }
</style>
{{-- <script>
    const toggleBtn = document.querySelector("a[data-toggle='push-menu']")
    const body = document.querySelector("body")
    console.log(body)

    toggleBtn.addEventListener('click', function(){
        console.log(body.classList)
        if(body.classList.contains("sidebar-open")){
            console.log("open")
            body.classList.remove("sidebar-open")
        } else {
            console.log("close")
            body.classList.add("sidebar-open")
        }
    })
</script> --}}
