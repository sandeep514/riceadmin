@extends('layouts.main')

@section('content')
    <div class="modal fade" id="sample-register-modal" data-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Create New Sample Register</h4>
                </div>
                <div class="modal-body">
                    <h4 class="text-center">Create new or from existing sample?</h4>
                    <div class="row">
                        <div class="col-md-12">
                            <label>Existing Sample</label>
                            {!! Form::select('filed_runner_samples',\App\Sample::samples(),null,['class'=>'form-control existing-sample','placeholder'=>'Select Sample']) !!}
                        </div>
                        <div class="col-md-12 text-center" style="padding-top: 10px; padding-bottom: 10px">
                            OR
                        </div>
                        <div class="col-md-12 text-center">
                            <button class="btn btn-primary create_new">Create New</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>

    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                Create Sample Register
                <small>Create</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="#">Sample Registers</a></li>
                <li class="active">Create</li>
            </ol>
        </section>
        <section class="content">
            <div class="row">
                <!-- left column -->
                <div class="col-md-12">
                    <!-- general form elements -->
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">Sample Register Details</h3>
                        </div>
                        <!-- /.box-header -->
                        @if(isset($sampleModel))
                            @php
                                $sampleModel->date = \Carbon\Carbon::parse($sampleModel->date)->format('d-m-Y');
                            @endphp
                            {!! Form::model($sampleModel, ['route'=>'save.sample-register','files'=>true]) !!}
                        @else
                            {!! Form::open(['route'=>'save.sample-register','files'=>true]) !!}
                        @endif
                        @include('sample-registers._form')
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('sample-registers') }}" class="btn btn-danger">Cancel</a>
                        </div>
                        {!! Form::close() !!}
                    </div>

                    <!-- /.box -->
                </div>
            </div>
        </section>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function(){
            @if(request()->sample == null)
                $('#sample-register-modal').modal('show').modal({
                    backdrop: 'static',
                    keyboard: false
                });
            @endif
            $('.existing-sample').change(function(){
                if($(this).val().trim() != ''){
                    window.location.href = '{{ route('create.sample-register') }}/'+$(this).val()
                }
            });
            $('.create_new').click(function(){
                window.location.href = '{{ route('create.sample-register') }}/new';
            });
        });
    </script>
@endsection
