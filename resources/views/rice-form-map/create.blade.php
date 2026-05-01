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
    var riceNamesUrl = "{{ route('ajax.rice-form-map.rice-names', ':type') }}";
    var formsUrl     = "{{ route('ajax.rice-form-map.forms', ':type') }}";
    var wandsUrl     = "{{ route('ajax.rice-form-map.wands', ':riceNameId') }}";

    // Init select2
    function initSelect2(el, placeholder){
        $(el).select2({ placeholder: placeholder, allowClear: true });
    }
    initSelect2('#rice_name_id', 'Select Rice Name');
    initSelect2('#wand_ids',     'Select Wand Types');

    // When rice type changes → load rice names + load forms
    $('#rice_type').on('change', function(){
        var type = $(this).val();

        // Reset dependent fields
        $('#rice_name_id').val(null).trigger('change').prop('disabled', true);
        $('#form_ids').val(null).prop('disabled', true);
        $('#wand_ids').val(null).trigger('change').prop('disabled', true).empty();

        if (!type) return;

        // Load Rice Names
        var url = riceNamesUrl.replace(':type', type);
        $.get(url, function(data){
            $('#rice_name_id').empty().append('<option value="">-- Select Rice Name --</option>');
            $.each(data, function(id, name){
                $('#rice_name_id').append('<option value="'+id+'">'+name+'</option>');
            });
            $('#rice_name_id').prop('disabled', false).trigger('change.select2');
        });

        // Load Rice Forms by type
        var formUrl = formsUrl.replace(':type', type);
        $.get(formUrl, function(data){
            $('#form_ids').empty().append('<option value="">-- Select Rice Form --</option>');
            $.each(data, function(id, name){
                $('#form_ids').append('<option value="'+id+'">'+name+'</option>');
            });
            $('#form_ids').prop('disabled', false);
        });
    });

    // When rice name changes → load wand types with values
    $('#rice_name_id').on('change', function(){
        var riceNameId = $(this).val();
        $('#wand_ids').val(null).trigger('change').prop('disabled', true).empty();
        if (!riceNameId) return;

        var url = wandsUrl.replace(':riceNameId', riceNameId);
        $.get(url, function(data){
            $('#wand_ids').empty();
            $.each(data, function(id, label){
                $('#wand_ids').append('<option value="'+id+'">'+label+'</option>');
            });
            $('#wand_ids').prop('disabled', false).trigger('change.select2');
        });
    });
});
</script>
@endsection
