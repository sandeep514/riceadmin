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
                Vendor
                <small>Vendor</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="{{ route('documents') }}">Vendor</a></li>
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
                            <h3 class="box-title">Vendor</h3>
                        </div>
                        <!-- /.box-header -->
                        {!! Form::open(['route'=>'bag.vendor.master.save','files'=>true]) !!}
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group col-md-12 @error('file') has-error @enderror" style="padding: 0">
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
                                <div class="row">
                                    <div class="col-md-12">
                                        <ul>
                                            @foreach( $vendorType as $k => $v )
                                                <li style="list-style: none;float: left;min-width: 33%;"> 
                                                    <label>
                                                        <input type="radio" {{ ( $k == 1 )? 'checked' : '' }} name="vendorType" value="{{ $k }}" style="margin-right: 7px"> {{ $v }}
                                                    </label>
                                                </li>
                                            @endforeach
                                        </ul>
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
                                <th>Vendor name</th>
                                <th>Vendor Address</th>
                                <th>Contact Person</th>
                                <th>Contact number</th>
                                <th>Specialised</th>
                                <th>Email</th>
                                <th>Vendor Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bagvendor as $k => $v)
                                <tr>
                                    <td>{{ $v->vendor_name }}</td>
                                    <td>{{ $v->vendor_address }}</td>
                                    <td>{{ $v->contact_person }}</td>
                                    <td>{{ $v->contact_number }}</td>
                                    <td>{{ $v->specialised }}</td>
                                    <td>{{ $v->email }}</td>
                                    <td>{{ App\BagVendors::vendorType()[$v->vendor_type] }}</td>
                                </tr>
                            @endforeach
                           
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>Vendor name</th>
                                <th>Vendor Address</th>
                                <th>Contact Person</th>
                                <th>Contact number</th>
                                <th>Specialised</th>
                                <th>Email</th>
                                <th>Vendor Type</th>
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
                pageLength : 20,
            } );
        } );
    </script>
@endsection

