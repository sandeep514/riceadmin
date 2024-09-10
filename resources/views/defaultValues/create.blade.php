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
                        {!! Form::open(['route'=>'dollarExcel.default.value.master.save','files'=>true]) !!}

                            <div class="col-md-7">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group col-md-4" >
                                            {!! Form::label('file','local Charges Default Value') !!}
                                            {!! Form::number('localcharges',( $defaultvalue != null )? $defaultvalue->localcharges : 0 ,[ 'style' => "margin-right: 10px" ,'required' => 'required' ,'class'=>'form-control','id'=>'localcharges']) !!}
                                        </div>
                                        <div class="form-group col-md-4" >
                                            {!! Form::label('file','Finance Default Value') !!}
                                            {!! Form::number('financecost',( $defaultvalue != null )? $defaultvalue->financecost : 0 ,[ 'style' => "margin-right: 10px" ,'required' => 'required' ,'class'=>'form-control','id'=>'financecost']) !!}
                                        </div>
                                        <div class="form-group col-md-4" >
                                            {!! Form::label('file','Dollar current Default Value') !!}
                                            {!! Form::text('dollarvalue',( $defaultvalue != null )? $defaultvalue->dollarvalue : 0 ,[ 'style' => "margin-right: 10px" ,'required' => 'required' ,'class'=>'form-control','id'=>'dollarvalue']) !!}
                                        </div>
                                        <div class="form-group col-md-4" >
                                            {!! Form::label('file','Bag Cost') !!}
                                            {!! Form::number('bagcost',( $defaultvalue != null )? $defaultvalue->bagcost : 0 ,[ 'style' => "margin-right: 10px" ,'required' => 'required' ,'class'=>'form-control','id'=>'bagcost']) !!}
                                        </div>
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

                    {{-- <table id="example" class="display" style="width:100%">
                        <thead>
                            <tr>
                                <th>Local Charges</th>
                                <th>Finance Cost</th>
                                <th>Dollar Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($defaultvalue as $k => $v)
                                <tr>
                                    <td>{{ $v->localcharges }}</td>
                                    <td>{{ $v->financecost }}</td>
                                    <td>{{ $v->dollarvalue }}</td>
                                </tr>
                            @endforeach
                           
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>Local Charges</th>
                                <th>Finance Cost</th>
                                <th>Dollar Rate</th>
                            </tr>
                        </tfoot>
                    </table> --}}

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

