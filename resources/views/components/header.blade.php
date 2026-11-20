<header class="main-header">
    <!-- Logo -->
    <a href="javascript:void(0)" class="logo">
        <!-- mini logo for sidebar mini 50x50 pixels -->
        <span class="logo-mini"><b>S</b>NTC</span>
        <!-- logo for regular state and mobile devices -->
        <span class="logo-lg"><b>SNTC</b> Rice Sourcing</span>
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

@if(!empty($backupOverdue) && auth()->check() && (int) auth()->user()->role === 2)
<div class="modal fade" id="backup-overdue-modal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false" aria-labelledby="backup-overdue-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="margin-top: 12vh;">
        <div class="modal-content" style="border-top: 4px solid #dd4b39;">
            <div class="modal-header" style="border-bottom: none; padding-bottom: 0;">
                <h4 class="modal-title" id="backup-overdue-modal-title" style="font-weight: 700; color: #dd4b39;">
                    <i class="fa fa-exclamation-triangle"></i> Database backup required
                </h4>
            </div>
            <div class="modal-body" style="padding-top: 12px;">
                <p style="font-size: 15px; margin-bottom: 10px;">
                    A database backup is overdue and must be downloaded before you continue.
                </p>
                @if(!empty($lastBackupAt))
                    <p class="text-muted" style="margin-bottom: 8px;">
                        Last backup was <strong>{{ (int) $lastBackupDaysAgo }} day{{ (int) $lastBackupDaysAgo === 1 ? '' : 's' }}</strong> ago
                        ({{ $lastBackupAt->format('d M Y H:i') }}).
                        Backups are required at least every {{ (int) \App\DatabaseBackupLog::OVERDUE_AFTER_DAYS }} days.
                    </p>
                @else
                    <p class="text-muted" style="margin-bottom: 8px;">
                        No backup has been downloaded yet. Please download one now.
                    </p>
                @endif
                <p id="backup-overdue-modal-status" class="text-muted" style="display:none; margin-top: 12px; margin-bottom: 0;">
                    <i class="fa fa-spinner fa-spin"></i>
                    Preparing backup… keep this window open. The dialog will close when the backup is recorded.
                </p>
            </div>
            <div class="modal-footer" style="border-top: none; text-align: center; padding-top: 0;">
                <a href="{{ route('database.backup.download') }}"
                   id="backup-overdue-download-btn"
                   class="btn btn-danger btn-lg"
                   style="min-width: 240px;">
                    <i class="fa fa-download"></i> Download database backup
                </a>
            </div>
        </div>
    </div>
</div>
@endif

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
    #backup-overdue-modal .modal-dialog-centered {
        display: flex;
        align-items: center;
        min-height: calc(100% - 1rem);
    }
    #backup-overdue-modal .modal-content {
        width: 100%;
        box-shadow: 0 12px 40px rgba(0,0,0,.35);
    }
    body.backup-overdue-locked {
        overflow: hidden !important;
    }
</style>

@if(!empty($backupOverdue) && auth()->check() && (int) auth()->user()->role === 2)
<script>
(function () {
    function bootBackupModal() {
        if (typeof jQuery === 'undefined') {
            setTimeout(bootBackupModal, 50);
            return;
        }
        var $ = jQuery;
        var $modal = $('#backup-overdue-modal');
        if (!$modal.length) {
            return;
        }

        var statusUrl = @json(route('database.backup.status'));
        var pollTimer = null;
        var polling = false;

        function showModal() {
            $('body').addClass('backup-overdue-locked');
            $modal.modal({
                backdrop: 'static',
                keyboard: false,
                show: true
            });
        }

        function stopPolling() {
            if (pollTimer) {
                clearInterval(pollTimer);
                pollTimer = null;
            }
            polling = false;
        }

        function startPolling() {
            if (polling) {
                return;
            }
            polling = true;
            $('#backup-overdue-modal-status').show();
            $('#backup-overdue-download-btn')
                .addClass('disabled')
                .css('pointer-events', 'none')
                .html('<i class="fa fa-spinner fa-spin"></i> Preparing backup…');

            pollTimer = setInterval(function () {
                $.getJSON(statusUrl)
                    .done(function (resp) {
                        if (resp && resp.overdue === false) {
                            stopPolling();
                            $('#backup-overdue-modal-status').html(
                                '<i class="fa fa-check text-success"></i> Backup recorded. Reloading…'
                            );
                            setTimeout(function () {
                                window.location.reload();
                            }, 600);
                        }
                    });
            }, 2500);
        }

        // Never dismiss until backup succeeds (page reloads after status clears)
        $modal.on('hide.bs.modal', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            return false;
        });

        $(document).on('keydown.backupOverdue', function (e) {
            if (e.key === 'Escape' || e.keyCode === 27) {
                e.preventDefault();
                e.stopPropagation();
            }
        });

        $('#backup-overdue-download-btn').on('click', function () {
            startPolling();
        });

        // Header banner "Download now" should also start polling
        $(document).on('click', '.backup-overdue-banner a', function () {
            startPolling();
        });

        showModal();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bootBackupModal);
    } else {
        bootBackupModal();
    }
})();
</script>
@endif
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
