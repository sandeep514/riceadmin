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
                Quality Master
                <small>Create</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="{{ route('documents') }}">Quality Master</a></li>
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
                            <h3 class="box-title">Quality Master</h3>
                        </div>
                        <!-- /.box-header -->
                        {!! Form::open(['route'=>'dollarExcel.save.quality.master','files'=>true]) !!}
                            <div class="col-md-7">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group col-md-3 @error('file') has-error @enderror" style="padding: 0">
                                            {!! Form::label('file','Upload Excel*') !!}
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

                    <table id="example" class="display" style="width:100%">
                        <thead>
                            <tr>
                                <th>Quality</th>
                                <th>Quality Name</th>
                                <th>Quality Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($QualityMaster as $k => $v)
                                <tr>
                                    <td>{{ $v->quality }}</td>
                                    <td>{{ $v->quality_name }}</td>
                                    <td>{{ $v->quality_type }}</td>
                                </tr>
                            @endforeach
                           
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>Quality</th>
                                <th>Quality Name</th>
                                <th>Quality Type</th>
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
                
            } );
        } );
    </script>
@endsection

