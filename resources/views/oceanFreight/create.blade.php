@extends('layouts.main')

@section('content')
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">
<style type="text/css">
    .specifications{
        border: 1px solid #ccc;
    }
    .pd-0{
        padding: 0;
        margin-top: 4%
    }
    .center{
        text-align: center
    }
    .p-0{
        padding: 0;
    }
    .m-0{
        margin: 0;
    }
    .spec{
        padding: 11px;
        border-bottom: 1px solid #ccc;
        margin-bottom: 15px;
    }
</style>
    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                Gallery
                <small>Create</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="{{ route('documents') }}">Default Master</a></li>
                <li class="active">Upload</li>
            </ol>
        </section>
        <section class="content">
            <div class="row">
                <!-- left column -->
                <div class="col-md-12">
                    <!-- general form elements -->
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">Default Master</h3>
                        </div>
                        <!-- /.box-header -->
                        {!! Form::open(['route'=>'dollarExcel.save.ocean.freight','files'=>true]) !!}
                            <div class="col-md-7">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group col-md-12 @error('file') has-error @enderror" style="padding: 0">
                                            {!! Form::label('file','Upload ocean freight Excel*') !!}
                                            {!! Form::file('file',null,['required' => 'required' ,'class'=>'form-control','id'=>'file']) !!}
                                        </div>
                                        @error('file')
                                            <span class="help-block text-danger" role="alert">
                                                {{ $message }}
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="box-footer">
                                        <button type="submit" class="btn btn-primary">Save</button>
                                        <a href="{{ route('documents') }}" class="btn btn-danger">Cancel</a>
                                    </div>      
                                </div>
                            </div>
                        {!! Form::close() !!}
                    </div>

                    <table id="example" class="display" style="width: 100%">
                        <thead>
                            <tr>
                                <th>Sno</th>
                                <th>Region</th>
                                <th>Country</th>
                                <th>Port</th>
                                <th>Freight 21MT</th>
                                <th>Freight 25MT</th>
                                <th>Freight PMT</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($OceanFreight as $k => $v)
                                <tr>
                                    <td>{{ $v->sno }}</td>
                                    <td>{{ $v->region }}</td>
                                    <td>{{ $v->country }}</td>
                                    <td>{{ $v->port }}</td>
                                    <td>${{ $v->freight_21MT }}</td>
                                    <td>${{ $v->freight_25MT }}</td>
                                    <td>${{ $v->freight_25MT_1MT }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>Sno</th>
                                <th>Region</th>
                                <th>Country</th>
                                <th>Port</th>
                                <th>Freight 21MT</th>
                                <th>Freight 25MT</th>
                                <th>Freight PMT</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </section>

    </div>
@endsection
@section('scripts')
<script type="text/javascript" src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $('#example').DataTable( {
                pageLength : 50,
            } );
        } );
    </script>
@endsection