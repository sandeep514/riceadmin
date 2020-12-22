@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                Permissions
            </h1>
            <ol class="breadcrumb">
                <li><a href="javascript:void(0)"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="javascript:void(0)"><i class="fa fa-dashboard"></i> Permissions</a></li>
            </ol>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-xs-12">
                    <div class="box">
                        <div class="box-header">
                            <h3 class="box-title">Module: {{ \App\Module::find(request()->module_id)->name }}</h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-12">
                                    {!! Form::open(['route'=>'permissions.save']) !!}
                                    {!! Form::hidden('module_id',request()->module_id) !!}
                                    {!! Form::hidden('role_id',request()->role_id) !!}
                                    @if(request()->role_id == 3)
                                        @php
                                            $selectedDesignation = $permissions->first();
                                            if($selectedDesignation != null){
                                                $selectedDesignation = $selectedDesignation->designation;
                                            }else{
                                                $selectedDesignation = null;
                                            }
                                        @endphp
                                        <div class="row">
                                            <div class="col-md-5 @error('designation') has-error @enderror">
                                                <label>Select Designation</label>
                                                {!! Form::select('designation',\App\Designation::designationsList(),$selectedDesignation,['class'=>'form-control','placeholder'=>'Select Designation']) !!}
                                                @error('designation')
                                                    <span class="help-block text-danger">
                                                        {{ $message }}
                                                    </span>
                                                @enderror
                                            </div>
                                        </div>
                                    @endif
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Action</th>
                                                <th>Enable/Disable</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($routes as $action => $name)
                                                <tr>
                                                    <td>{{ $loop->index + 1 }}</td>
                                                    <td>{{ ucfirst($action) }}</td>
                                                    <td>
                                                        @php
                                                            $isHavePermission = null;
                                                            $permissionedRoute = $permissions->where('action',$action)->where('status',1)->first();
                                                            if($permissionedRoute != null){
                                                                $isHavePermission = true;
                                                            }
                                                        @endphp
                                                        {!! Form::hidden('permissions['.$action.']['.$name.']','off') !!}
                                                        {!! Form::checkbox('permissions['.$action.']['.$name.']',null,$isHavePermission) !!}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                    {!! Form::submit('Save Permissions',['class'=>'btn btn-primary']) !!}
                                </div>
                            </div>
                        </div>
                        <!-- /.box-body -->
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('js/modules.js') }}"></script>
@endsection
