@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                Edit Quality
                <small>Edit</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="#">Qualities</a></li>
                <li class="active">Edit Quality</li>
            </ol>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-md-12">
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">Quality Details</h3>
                        </div>
                        {!! Form::open(['route'=>'master.update.city','method'=>'post']) !!}
                            <input type="hidden" name="id" value="{{ $livePrices->id }}">
                            <div class="form-group col-md-4 ">
                                {!! Form::label('state','State*') !!}
                                {!! Form::text('state' ,$livePrices->state , ['class' => 'form-control']) !!}
                            </div>
                            <div class="box-footer">
                                <button type="submit" class="btn btn-primary">Save</button>
                            </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
