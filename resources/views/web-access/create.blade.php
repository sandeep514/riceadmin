@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                Create Web Access
                <small>Create</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="{{ route('web-access') }}">Web Access</a></li>
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
                            <h3 class="box-title">Web Access Details</h3>
                        </div>
                        <!-- /.box-header -->
                        {!! Form::open(['route'=>'save.web-access']) !!}
                        @include('web-access._form')
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary">Save</button>
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

            function loadPlan(roleId, categoryId) {
                if (roleId && categoryId) {
                    $.ajax({
                        url: "web-access/get-plan",
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
                var categorySelect = $('#category_id');

                // Reset plan
                $('#plan_id').val('');
                $('#plan_display').val('');
                
                if(roleId) {
                    categorySelect.prop('disabled', false);
                    categorySelect.html('<option value="">Loading categories...</option>');
                    
                    $.ajax({
                        url: "web-access/get-categories",
                        type: "GET",
                        data: {role_id: roleId},
                        success: function(data) {
                            categorySelect.empty();
                            categorySelect.append('<option value="">Select Category</option>');
                            
                            if(Object.keys(data).length > 0) {
                                $.each(data, function(key, value) {
                                    categorySelect.append('<option value="'+key+'">'+value+'</option>');
                                });
                            } else {
                                categorySelect.append('<option value="">No categories available</option>');
                            }
                        },
                        error: function() {
                            categorySelect.empty();
                            categorySelect.append('<option value="">Error loading categories</option>');
                        }
                    });
                } else {
                    categorySelect.prop('disabled', true);
                    categorySelect.empty();
                    categorySelect.append('<option value="">Select Category</option>');
                }
            });

            // Load plan when category is selected
            $('#category_id').on('change', function() {
                var roleId     = $('#role_id').val();
                var categoryId = $(this).val();
                loadPlan(roleId, categoryId);
            });
        });
    </script>
@endsection

