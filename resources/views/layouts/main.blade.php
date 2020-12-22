<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <base href="{{ asset('web') }}/">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Rice Brokerage::Admin Panel</title>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.7 -->
    <link rel="stylesheet" href="{{ asset('web/bower_components/bootstrap/dist/css/bootstrap.min.css') }}">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('web/bower_components/font-awesome/css/font-awesome.min.css') }}">
    <!-- Ionicons -->
    <link rel="stylesheet" href="{{ asset('web/bower_components/Ionicons/css/ionicons.min.css') }}">

    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('web/bower_components/select2/dist/css/select2.min.css') }}">
    <!-- iCheck -->
    <link rel="stylesheet" href="{{ asset('web/plugins/iCheck/square/blue.css') }}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset('web/dist/css/AdminLTE.min.css') }}">
    <!-- AdminLTE Skins. Choose a skin from the css/skins
         folder instead of downloading all of them to reduce the load. -->
    <link rel="stylesheet" href="{{ asset('web/dist/css/skins/_all-skins.min.css') }}">
    <!-- Morris chart -->
    <link rel="stylesheet" href="{{ asset('web/bower_components/morris.js/morris.css') }}">
    <!-- jvectormap -->
    <link rel="stylesheet" href="{{ asset('web/bower_components/jvectormap/jquery-jvectormap.css') }}">
    <!-- Date Picker -->
    <link rel="stylesheet" href="{{ asset('web/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') }}">
    <!-- Daterange picker -->
    <link rel="stylesheet" href="{{ asset('web/bower_components/bootstrap-daterangepicker/daterangepicker.css') }}">
    <!-- bootstrap wysihtml5 - text editor -->
    <link rel="stylesheet" href="{{ asset('web/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css') }}">

    <link rel="stylesheet" href="{{ asset('css/custom.css?ref='.rand(1111,9999)) }}" />

    <link href="{{ asset('web/plugins/toastr/toastr.min.css') }}" rel="stylesheet">
    <!-- Datatable -->
    <link rel="stylesheet" href="{{ asset('web/bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.22/css/jquery.dataTables.min.css">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>


    <![endif]-->

    <!-- Google Font -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic">
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

    @include('components.header')
    <!-- Left side column. contains the logo and sidebar -->
    @include('components.sidebar')
    @if(session()->has('message'))
        <div class="alert alert-success" style="z-index: 9999999;position: absolute;right: 0;width: 250px;">
            {{ session()->get('message') }}
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger" style="z-index: 9999999;position: absolute;right: 0;width: 250px;">
            {{ $errors->first('error') }}
        </div>
    @endif
    <!-- Content Wrapper. Contains page content -->
    @yield('content')
    <!-- /.content-wrapper -->
    @include('components.footer')

    <!-- Control Sidebar -->
    @include('components.right-sidebar')
    <!-- /.control-sidebar -->
    <!-- Add the sidebar's background. This div must be placed
         immediately after the control sidebar -->
    <div class="control-sidebar-bg"></div>
</div>
<!-- ./wrapper -->
<script type="text/javascript">
    window.route = "{{ url('/') }}/administrator";
    // $.ajaxSetup({
    //     headers: {
    //         'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    //     }
    // });
</script>
@include('components.scripts')
<style>
    .dropdown-menu{
        z-index: 9999 !important;
    }

</style>
<script>
    $(document).ready(function(){
        setTimeout(() =>{
            $('.alert').fadeOut()
        } , 3000);
    });
</script>
</body>
</html>
