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
                        {!! Form::open(['route'=>'dollarExcel.save','files'=>true]) !!}
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
                                <th>Bag Size</th>
                                <th>Bag Type</th>
                                <th>Bag Cost</th>
                                <th>Local Freight</th>
                                <th>cha</th>
                                <th>Bank Charges</th>
                                <th>ins</th>
                                <th>total</th>
                                <th>Conversion Rate</th>
                                <th>PMT USD</th>
                                <th>Created At</th>

                            </tr>
                        </thead>
                        <tbody>
                            @foreach($usdPrices as $k => $v)
                                <tr>
                                    <td>{{ $v->bag_size }}</td>
                                    <td>{{ $v->bag_type }}</td>
                                    <td>{{ $v->bag_cost }}</td>
                                    <td>{{ $v->local_freight }}</td>
                                    <td>{{ $v->cha }}</td>
                                    <td>{{ $v->bank_charges }}</td>
                                    <td>{{ $v->ins }}</td>
                                    <td>{{ $v->total }}</td>
                                    <td>{{ $v->conversion_rate }}</td>
                                    <td>{{ $v->PMT_USD }}</td>
                                    <td>{{ $v->created_at }}</td>
                                </tr>
                            @endforeach
                           
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>Bag Size</th>
                                <th>Bag Type</th>
                                <th>Bag Cost</th>
                                <th>Local Freight</th>
                                <th>cha</th>
                                <th>Bank Charges</th>
                                <th>ins</th>
                                <th>total</th>
                                <th>Conversion Rate</th>
                                <th>PMT USD</th>
                                <th>Created At</th>
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

