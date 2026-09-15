@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                Destination Masters
                <small>Import Excel</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="javascript:void(0)"><i class="fa fa-dashboard"></i> Home</a></li>
                <li>Service Providers</li>
                <li>Masters</li>
                <li><a href="{{ route('vendor-destination-ports') }}">Destination Ports</a></li>
                <li class="active">Import</li>
            </ol>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-md-12">
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">Upload destination Excel</h3>
                        </div>
                        {!! Form::open(['route' => 'import.save.vendor-destination-ports', 'files' => true]) !!}
                            <div class="box-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group @error('file') has-error @enderror">
                                            {!! Form::label('file', 'Upload Excel*') !!}
                                            {!! Form::file('file', ['required' => 'required', 'class' => 'form-control', 'id' => 'file', 'accept' => '.xlsx,.xls,.csv']) !!}
                                            @error('file')
                                                <span class="help-block text-danger" role="alert">{{ $message }}</span>
                                            @enderror
                                            <p class="help-block" style="margin-top: 10px;">
                                                Excel columns: <strong>Sr. No.</strong>, <strong>Regions</strong>, <strong>Country</strong>, <strong>Sea Port</strong>.
                                                Existing region / country / port names are skipped.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped" style="max-width: 720px;">
                                        <thead>
                                            <tr>
                                                <th>Sr. No.</th>
                                                <th>Regions</th>
                                                <th>Country</th>
                                                <th>Sea Port</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>1</td>
                                                <td>Africa</td>
                                                <td>Algeria</td>
                                                <td>Algeirs</td>
                                            </tr>
                                            <tr>
                                                <td>2</td>
                                                <td>Africa</td>
                                                <td>Algeria</td>
                                                <td>Annaba</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="box-footer">
                                <button type="submit" class="btn btn-primary">Import</button>
                                <a href="{{ route('vendor-destination-ports') }}" class="btn btn-danger">Cancel</a>
                            </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
