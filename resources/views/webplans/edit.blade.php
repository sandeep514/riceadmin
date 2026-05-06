@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                Edit Web Plan
                <small>Edit</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="#">Web Plan</a></li>
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
                            <h3 class="box-title">Web Plan Details</h3>
                        </div>
                        <!-- /.box-header -->
                        {!! Form::open(['route'=>'web.plans.update']) !!}
                            @include('webplans._editForm')
                            <div class="box-footer">
                                <button type="submit" class="btn btn-primary">Save</button>
                                <a href="{{ route('roles') }}" class="btn btn-danger">Cancel</a>
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
    var savedCategoryId = "{{ $data->category_id }}";

    function slugify(text) {
        return text.toString().toLowerCase().trim().replace(/\s+/g, '_').replace(/[^\w_]/g, '');
    }

    function updatePlanTitle() {
        var roleText = $('#role_id option:selected').text().trim();
        var catText  = $('#category_id option:selected').text().trim();
        var invalid  = ['', 'Select Role', 'Select Role first', 'Select Category', 'No categories available', 'Loading...'];
        if (invalid.indexOf(roleText) === -1 && invalid.indexOf(catText) === -1) {
            $('#plan_title').val(slugify(roleText) + '_' + slugify(catText));
        }
    }

    function loadCategories(roleId, preSelectId) {
        var catSel = $('#category_id');
        catSel.empty().append('<option value="">Loading...</option>');

        if (roleId) {
            $.ajax({
                url: window.route+"/web-access/get-categories",
                type: 'GET',
                data: { role_id: roleId },
                success: function(data) {
                    catSel.empty().append('<option value="">Select Category</option>');
                    if (Object.keys(data).length > 0) {
                        $.each(data, function(key, value) {
                            var selected = (preSelectId && preSelectId == key) ? 'selected' : '';
                            catSel.append('<option value="' + key + '" ' + selected + '>' + value + '</option>');
                        });
                    } else {
                        catSel.append('<option value="">No categories available</option>');
                    }
                },
                error: function() {
                    catSel.empty().append('<option value="">Error loading categories</option>');
                }
            });
        } else {
            catSel.empty().append('<option value="">Select Role first</option>');
        }
    }

    // On page load: pre-load categories for the saved role and pre-select saved category
    var initialRole = $('#role_id').val();
    if (initialRole) {
        loadCategories(initialRole, savedCategoryId);
    }

    $('#role_id').on('change', function() {
        loadCategories($(this).val(), null);
        $('#plan_title').val('');
    });

    $('#category_id').on('change', function() {
        updatePlanTitle();
    });
});
</script>
@endsection
