@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>Edit Rice Form Mapping <small>Edit</small></h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="{{ route('rice-form-map') }}">Rice Form Map</a></li>
                <li class="active">Edit</li>
            </ol>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-md-12">
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">Rice Form Map Details</h3>
                        </div>
                        {!! Form::model($model, ['route'=>['update.rice-form-map',$model->id],'method'=>'put']) !!}
                            @include('rice-form-map._form')
                            <div class="box-footer">
                                <button type="submit" class="btn btn-primary">Update</button>
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

    // Existing saved values for pre-selection
    var savedRiceNameId = "{{ $model->rice_name_id }}";
    var savedFormId     = "{{ $model->form_ids }}";
    var savedWandIds    = {!! json_encode($model->wand_ids ?? []) !!};

    // Init select2
    function initSelect2(el, placeholder){
        $(el).select2({ placeholder: placeholder, allowClear: true });
    }
    initSelect2('#rice_name_id', 'Select Rice Name');
    initSelect2('#wand_ids',     'Select Wand Types');

    // On edit page load — if rice_type already set, enable rice name & forms dropdowns
    var currentType = $('#rice_type').val();
    if (currentType) {
        $('#rice_name_id').prop('disabled', false);
        $('#form_ids').prop('disabled', false);
    }
    // Also pre-load wands if rice_name_id is already set
    if (savedRiceNameId) {
        var wUrl = wandsUrl.replace(':riceNameId', savedRiceNameId);
        $.get(wUrl, function(data){
            $('#wand_ids').empty();
            $.each(data, function(id, label){
                var selected = (savedWandIds.indexOf(parseInt(id)) !== -1) ? ' selected' : '';
                $('#wand_ids').append('<option value="'+id+'"'+selected+'>'+label+'</option>');
            });
            $('#wand_ids').prop('disabled', false).trigger('change.select2');
            savedWandIds = [];
        });
    }

    // When rice type changes → reload rice names + forms (cascade)
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
                var selected = (id == savedRiceNameId) ? ' selected' : '';
                $('#rice_name_id').append('<option value="'+id+'"'+selected+'>'+name+'</option>');
            });
            $('#rice_name_id').prop('disabled', false).trigger('change.select2');
            savedRiceNameId = ''; // clear after first use
        });

        // Load Rice Forms by type
        var formUrl = formsUrl.replace(':type', type);
        $.get(formUrl, function(data){
            $('#form_ids').empty().append('<option value="">-- Select Rice Form --</option>');
            $.each(data, function(id, name){
                var selected = (id == savedFormId) ? ' selected' : '';
                $('#form_ids').append('<option value="'+id+'"'+selected+'>'+name+'</option>');
            });
            $('#form_ids').prop('disabled', false);
            savedFormId = ''; // clear after first use
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
                var selected = (savedWandIds.indexOf(parseInt(id)) !== -1) ? ' selected' : '';
                $('#wand_ids').append('<option value="'+id+'"'+selected+'>'+label+'</option>');
            });
            $('#wand_ids').prop('disabled', false).trigger('change.select2');
            savedWandIds = [];
        });
    });
});
</script>
@endsection
