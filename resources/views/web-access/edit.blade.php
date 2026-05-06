@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                Edit Web Access
                <small>Edit</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="{{ route('web-access') }}">Web Access</a></li>
                <li class="active">Edit</li>
            </ol>
        </section>
        <section class="content">
            <div class="row">
                <!-- left column -->
                <div class="col-md-12">
                    <!-- general form elements -->
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">Web Access Details</h3>
                        </div>
                        <!-- /.box-header -->
                        {!! Form::open(['route'=>['update.web-access', $access->id], 'method'=>'PUT']) !!}
                        @include('web-access._form', ['access' => $access])
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary">Update</button>
                            <a href="{{ route('web-access') }}" class="btn btn-danger">Cancel</a>
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
            var selectedCategoryId = "{{ $access->category_id ?? '' }}";

            function loadPlan(roleId, categoryId) {
                if (roleId && categoryId) {
                    $.ajax({
                        url: "{{ url('administrator/web-access/get-plan') }}",
                        type: "GET",
                        data: { role_id: roleId, category_id: categoryId },
                        success: function(data) {
                            if (data && data.found) {
                                $('#plan_id').val(data.id);
                                $('#plan_display').val(data.title);
                            } else {
                                $('#plan_id').val('');
                                $('#plan_display').val('No plan found for this Role + Category');
                            }
                        },
                        error: function() {
                            $('#plan_id').val('');
                            $('#plan_display').val('Error loading plan');
                        }
                    });
                } else {
                    $('#plan_id').val('');
                    $('#plan_display').val('');
                }
            }

            // Load categories when role is selected
            $('#role_id').on('change', function() {
                var roleId = $(this).val();
                $('#plan_id').val('');
                $('#plan_display').val('');

                if(roleId) {
                    $.ajax({
                        url: "{{ url('administrator/web-access/get-categories') }}",
                        type: "GET",
                        data: {role_id: roleId},
                        success: function(data) {
                            $('#category_id').empty();
                            $('#category_id').append('<option value="">Select Category</option>');
                            $.each(data, function(key, value) {
                                var selected = (key == selectedCategoryId) ? 'selected' : '';
                                $('#category_id').append('<option value="'+key+'" '+selected+'>'+value+'</option>');
                            });
                            // After loading categories, load plan for pre-selected category
                            if (selectedCategoryId) {
                                loadPlan(roleId, selectedCategoryId);
                            }
                        }
                    });
                } else {
                    $('#category_id').empty();
                    $('#category_id').append('<option value="">Select Category</option>');
                }
            });

            // Load plan when category changes
            $('#category_id').on('change', function() {
                loadPlan($('#role_id').val(), $(this).val());
            });

            // Trigger on page load if role is already selected
            if($('#role_id').val()) {
                $('#role_id').trigger('change');
            }
        });
    </script>
@endsection

