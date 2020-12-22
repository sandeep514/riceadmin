@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                Modules
            </h1>
            <ol class="breadcrumb">
                <li><a href="javascript:void(0)"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="javascript:void(0)"><i class="fa fa-dashboard"></i> Modules</a></li>
            </ol>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-xs-12">
                    <div class="box">
                        <div class="box-header">
                            <h3 class="box-title">Modules List</h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <label>Select Role</label>
                                    {!! Form::select('role',$roles,request()->role_id,['class'=>'form-control role','placeholder'=>'Select Role']) !!}
                                </div>
                                <div class="col-md-3">
                                    {!! Form::submit('View Modules',['class'=>'btn btn-primary view_modules','style'=>'margin-top: 25px']) !!}
                                </div>
                            </div>
                            @if(isset(request()->role_id))
                                <div class="divider"></div>
                                {!! Form::open(['route'=>'modules.save']) !!}
                                    {!! Form::hidden('role',request()->role_id) !!}
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Module</th>
                                                    <th>Enable/Disable</th>
                                                    <th>Permissions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($modules as $key => $module)
                                                    <tr>
                                                        <td>{{ $loop->index+1 }}</td>
                                                        <td>{{ $module->name }}</td>
                                                        <td>
                                                            {!! Form::hidden('enable_disable['.$module->slug.']','off') !!}
                                                            @php
                                                                $activeStatus = false;
                                                                if(!empty($activeModules)){
                                                                    if(isset($activeModules[$module->slug]) && $activeModules[$module->slug] == 'on'){
                                                                        $activeStatus = true;
                                                                    }
                                                                }
                                                            @endphp
                                                            {!! Form::checkbox('enable_disable['.$module->slug.']',null,$activeStatus) !!}
                                                        </td>
                                                        <td>
                                                            @if($activeStatus)
                                                                <a href="{{ route('permissions',['module_id'=>$module->id,'role_id'=>request()->role_id]) }}" class="btn btn-danger btn-xs" target="_blank">Ser Permissions</a>
                                                            @else
                                                                <i class="text-light">Enable module first</i>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                        {!! Form::submit('Save Modules',['class'=>'btn btn-primary']) !!}
                                    </div>
                                {!! Form::close() !!}
                            @endif
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
