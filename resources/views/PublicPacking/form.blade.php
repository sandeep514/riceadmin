@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            {{ $isEdit ? 'Edit' : 'Add' }} Public Packing
            <small>{{ $isEdit ? 'Edit' : 'Create' }}</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="{{ route('public.packing.master') }}">Public Packing Master</a></li>
            <li class="active">{{ $isEdit ? 'Edit' : 'Add' }}</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Public Packing Details</h3>
                    </div>
                    {!! Form::open(['route' => $isEdit ? 'public.packing.master.update' : 'public.packing.master.store']) !!}
                        @if($isEdit)
                            <input type="hidden" name="id" value="{{ $data->id }}">
                        @endif
                        <div class="box-body">
                            <div class="row">
                                <div class="form-group col-md-4 @error('size') has-error @enderror">
                                    {!! Form::label('size', 'Size*') !!}
                                    <input type="text" name="size" class="form-control"
                                           value="{{ old('size', $data->size ?? '') }}"
                                           placeholder="e.g. 50Kg" required>
                                    @error('size')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-4 @error('packing') has-error @enderror">
                                    {!! Form::label('packing', 'Packing*') !!}
                                    <input type="text" name="packing" class="form-control"
                                           value="{{ old('packing', $data->packing ?? '') }}"
                                           placeholder="e.g. PP+inner" required>
                                    @error('packing')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-4 @error('order') has-error @enderror">
                                    {!! Form::label('order', 'Order') !!}
                                    <input type="number" name="order" class="form-control" min="1"
                                           value="{{ old('order', $data->order ?? '') }}"
                                           placeholder="{{ $isEdit ? '' : 'Auto if blank' }}">
                                    @error('order')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                                @if($isEdit)
                                    <div class="form-group col-md-4">
                                        {!! Form::label('status', 'Status') !!}
                                        <select name="status" class="form-control">
                                            <option value="1" {{ (int) old('status', $data->status) === 1 ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ (int) old('status', $data->status) === 0 ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Update' : 'Save' }}</button>
                            <a href="{{ route('public.packing.master') }}" class="btn btn-danger">Cancel</a>
                        </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
