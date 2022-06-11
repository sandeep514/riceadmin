@extends('layouts.main')

@section('content')

    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                Calculator
                <small>update</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="{{ route('designations') }}">Calculator</a></li>
                <li class="active">update</li>
            </ol>
        </section>
        <section class="content">
            <div class="row">
                <!-- left column -->
                <div class="col-md-12">
                    <!-- general form elements -->
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">Calculator Details</h3>
                        </div>
                        <!-- /.box-header -->
                        <form method="POST" action="{{ route('calculator.update') }}">
                            @csrf
                            @include('calculator._editform')
                            <div class="box-footer">
                                <button type="button" class="btn btn-info calculate">Calculate</button>
                                <button type="submit" class="btn btn-primary" style="float: right;">POST</button>
                            </div>                            
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </div>

@endsection
@section('scripts')
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript">

    </script>
@endsection