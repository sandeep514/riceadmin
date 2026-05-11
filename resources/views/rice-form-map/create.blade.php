@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>Create Rice Form Mapping <small>Create</small></h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="{{ route('rice-form-map') }}">Rice Form Map</a></li>
                <li class="active">Create</li>
            </ol>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-md-12">
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">Rice Form Map Details</h3>
                        </div>
                        {!! Form::open(['route'=>'save.rice-form-map']) !!}
                            @include('rice-form-map._form')
                            <div class="box-footer">
                                <button type="submit" class="btn btn-primary">Save</button>
                                <a href="{{ route('rice-form-map') }}" class="btn btn-danger">Cancel</a>
                            </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@section('javascript')
<script>
$(function(){
    var riceNamesUrl = window.route+"/rice-form-map/ajax/rice-names/:type";
    var formsUrl     = window.route+"/rice-form-map/ajax/forms/:type";
    var wandsUrl     = window.route+"/rice-form-map/ajax/wands/:riceNameId";

    // Init select2
    function initSelect2(el, placeholder){
        $(el).select2({ placeholder: placeholder, allowClear: true });
    }
    initSelect2('#rice_name_id', 'Select Rice Name');
    initSelect2('#wand_ids',     'Select Wand Types');

    // Load rice forms on page ready (milestone3 forms are not type-filtered)
    $.get(formsUrl.replace(':type', 'all'), function(data){
        $('#form_ids').empty().append('<option value="">-- Select Rice Form --</option>');
        $.each(data, function(id, name){
            $('#form_ids').append('<option value="'+id+'">'+name+'</option>');
        });
    });

    // When rice type changes → load rice names
    $('#rice_type').on('change', function(){
        var type = $(this).val();

        // Reset dependent fields
        $('#rice_name_id').val(null).trigger('change');
        $('#wand_ids').val(null).trigger('change').empty();

        if (!type) return;

        // Load Rice Names
        var url = riceNamesUrl.replace(':type', type);
        $.get(url, function(data){
            $('#rice_name_id').empty().append('<option value="">-- Select Rice Name --</option>');
            $.each(data, function(id, name){
                $('#rice_name_id').append('<option value="'+id+'">'+name+'</option>');
            });
            $('#rice_name_id').trigger('change.select2');
        });
    });

    // Check / Uncheck all wand types
    $(document).on('click', '#wand_check_all', function(){
        var allIds = $('#wand_ids option').map(function(){
            return this.value ? String(this.value) : null;
        }).get();
        $('#wand_ids').val(allIds).trigger('change');
    });
    $(document).on('click', '#wand_uncheck_all', function(){
        $('#wand_ids').val(null).trigger('change');
    });

    // When rice name changes → load wand types with values
    $('#rice_name_id').on('change', function(){
        var riceNameId = $(this).val();
        $('#wand_ids').val(null).trigger('change').empty();
        if (!riceNameId) return;

        var url = wandsUrl.replace(':riceNameId', riceNameId);
        $.get(url, function(data){
            $('#wand_ids').empty();
            var allWandIds = [];
            $.each(data, function(id, label){
                $('#wand_ids').append('<option value="'+id+'">'+label+'</option>');
                allWandIds.push(String(id));
            });
            // Pre-select all wand types by default on create
            $('#wand_ids').val(allWandIds).trigger('change');
        });
    });
});
</script>
@endsection
