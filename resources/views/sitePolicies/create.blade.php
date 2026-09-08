@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>Add Policy</h1>
        <ol class="breadcrumb">
            <li><a href="javascript:void(0)"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="{{ route('site.policies') }}">Site Policies</a></li>
            <li class="active">Create</li>
        </ol>
    </section>
    <section class="content">
        <div class="box box-primary">
            {!! Form::open(['route' => 'save.site.policy', 'method' => 'POST']) !!}
                @include('sitePolicies._form')
                <div class="box-footer">
                    <button type="submit" class="btn btn-primary">Save &amp; Generate PDF</button>
                    <a href="{{ route('site.policies') }}" class="btn btn-default">Cancel</a>
                </div>
            {!! Form::close() !!}
        </div>
    </section>
</div>
@endsection

@section('javascript')
<script>
(function () {
    var preset = document.getElementById('slug_preset');
    var slug = document.getElementById('slug');
    var title = document.getElementById('title');
    var labels = @json($predefined);
    if (preset && slug) {
        preset.addEventListener('change', function () {
            var v = preset.value;
            if (!v || v === '__custom__') {
                if (v === '__custom__') slug.focus();
                return;
            }
            slug.value = v;
            if (title && (!title.value || Object.values(labels).indexOf(title.value) !== -1)) {
                title.value = labels[v] || title.value;
            }
        });
    }

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
