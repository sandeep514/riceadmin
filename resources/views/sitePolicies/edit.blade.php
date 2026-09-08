@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>Edit Policy</h1>
        <ol class="breadcrumb">
            <li><a href="javascript:void(0)"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="{{ route('site.policies') }}">Site Policies</a></li>
            <li class="active">Edit</li>
        </ol>
    </section>
    <section class="content">
        <div class="box box-primary">
            {!! Form::model($model, ['route' => ['update.site.policy', $model->id], 'method' => 'PUT']) !!}
                @include('sitePolicies._form', ['model' => $model, 'predefined' => $predefined])
                <div class="box-footer">
                    <button type="submit" class="btn btn-primary">Update &amp; Regenerate PDF</button>
                    <a href="{{ route('site.policies') }}" class="btn btn-default">Cancel</a>
                    @if($model->pdf_path)
                        <a href="{{ asset($model->pdf_path) }}" class="btn btn-info pull-right" target="_blank" rel="noopener">
                            <i class="fa fa-file-pdf-o"></i> Current PDF
                        </a>
                    @endif
                </div>
            {!! Form::close() !!}
        </div>
    </section>
</div>
@endsection

@section('javascript')
<script>
(function () {
    if (window.jQuery && $.fn.wysihtml5) {
        $('#content').wysihtml5({
            toolbar: {
                'font-styles': true,
                'emphasis': true,
                'lists': true,
                'html': false,
                'link': true,
                'image': false,
                'color': false,
                'blockquote': true
            }
        });
    }
})();
</script>
@endsection
