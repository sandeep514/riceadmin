@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                Create Web Side Menu
                <small>Create</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="{{ route('web-side-menu') }}">Web Side Menu</a></li>
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
                            <h3 class="box-title">Web Side Menu Details</h3>
                        </div>
                        <!-- /.box-header -->
                        {!! Form::open(['route'=>'save.web-side-menu']) !!}
                        @include('web-side-menu._form')
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="{{ route('web-side-menu') }}" class="btn btn-danger">Cancel</a>
                        </div>
                        {!! Form::close() !!}
                    </div>
                    <!-- /.box -->
                </div>
            </div>
        </section>
    </div>
@endsection

@section('javascript')
<script>
    $(document).ready(function() {
        // Function to generate slug from text
        function generateSlug(text) {
            return text
                .toString()
                .toLowerCase()
                .trim()
                .replace(/\s+/g, '-')        // Replace spaces with -
                .replace(/[^\w\-]+/g, '')    // Remove all non-word chars
                .replace(/\-\-+/g, '-')      // Replace multiple - with single -
                .replace(/^-+/, '')          // Trim - from start of text
                .replace(/-+$/, '');          // Trim - from end of text
        }
        
        // Update slug preview when title changes
        $('#title').on('input', function() {
            var title = $(this).val();
            if (title) {
                var slug = generateSlug(title);
                $('#slug').val(slug);
            } else {
                $('#slug').val('');
            }
        });
    });
</script>
@endsection

