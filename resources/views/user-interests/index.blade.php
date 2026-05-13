@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>User interests <small>Web users filtered by rice preferences</small></h1>
            <ol class="breadcrumb">
                <li><a href="{{ route('home') }}"><i class="fa fa-dashboard"></i> Home</a></li>
                <li class="active">User interests</li>
            </ol>
        </section>
        <section class="content">
            @if(session('error'))
                <div class="alert alert-warning alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    {{ session('error') }}
                </div>
            @endif
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Filters</h3>
                    <p class="text-muted" style="margin-top:8px;margin-bottom:0;">
                        Choose rice type, then rice name, then form (from <code>web_rice_form_map</code>), then wand. Leave any field empty to include all values at that level.
                    </p>
                </div>
                <div class="box-body">
                    <form method="get" action="{{ route('user-interests') }}" id="user-interests-filter-form">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Rice type</label>
                                    <select name="rice_type" id="filter_rice_type" class="form-control">
                                        <option value="">— All —</option>
                                        <option value="basmati" {{ ($riceType ?? '') === 'basmati' ? 'selected' : '' }}>Basmati</option>
                                        <option value="non-basmati" {{ ($riceType ?? '') === 'non-basmati' ? 'selected' : '' }}>Non-basmati</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Rice name</label>
                                    <select name="rice_name_id" id="filter_rice_name_id" class="form-control">
                                        <option value="">— All —</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Rice form (milestone3)</label>
                                    <select name="form_id" id="filter_form_id" class="form-control">
                                        <option value="">— All —</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Wand (grade)</label>
                                    <select name="grade" id="filter_grade" class="form-control">
                                        <option value="">— All —</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Apply filters</button>
                        <a href="{{ route('user-interests') }}" class="btn btn-default">Reset</a>
                    </form>
                </div>
            </div>

            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Users ({{ $users->total() }} total)</h3>
                </div>
                <div class="box-body table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Matching interests</th>
                                <th style="width:90px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $u)
                                <tr>
                                    <td>{{ $u->name ?? '—' }}</td>
                                    <td>{{ $u->email ?? '—' }}</td>
                                    <td>{{ $u->phone ?: ($u->mobile ?? '—') }}</td>
                                    <td>
                                        @foreach($u->interestedMaps as $row)
                                            <div style="margin-bottom:6px;">
                                                <strong>{{ $row->riceName->name ?? '—' }}</strong>
                                                @if($row->riceForm)
                                                    · {{ $row->riceForm->name }}
                                                @endif
                                                @if($row->grade && $row->wandGrade)
                                                    ·
                                                    @if($row->wandGrade->getWandType)
                                                        {{ $row->wandGrade->getWandType->type }} — {{ $row->wandGrade->value }}
                                                    @else
                                                        {{ $row->wandGrade->value }}
                                                    @endif
                                                @elseif($row->grade)
                                                    · Wand #{{ $row->grade }}
                                                @endif
                                            </div>
                                        @endforeach
                                        @if($u->interestedMaps->isEmpty())
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('view.user', $u->id) }}" class="btn btn-info btn-xs">View</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No web users match these filters.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div style="margin-top:12px;">
                        {{ $users->onEachSide(1)->links() }}
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@section('javascript')
<script>
$(function () {
    var riceNamesUrl = window.route + '/rice-form-map/ajax/rice-names/';
    var formsUrl = window.route + '/user-interests/ajax/forms';
    var wandsUrl = window.route + '/user-interests/ajax/wands';

    var initialRiceNameId = @json($riceNameId ?? null);
    var initialFormId = @json($formId ?? null);
    var initialGrade = @json($grade ?? null);
    var initialRiceType = @json($riceType ?? null);
    var riceNameMeta = @json($riceNameMeta ?? null);

    if (riceNameMeta && riceNameMeta.type && !initialRiceType) {
        initialRiceType = riceNameMeta.type;
        $('#filter_rice_type').val(initialRiceType);
    }

    function loadRiceNames(type, selectedId, done) {
        var $sel = $('#filter_rice_name_id');
        $sel.empty().append('<option value="">— All —</option>');
        if (!type) {
            if (typeof done === 'function') done();
            return;
        }
        $.get(riceNamesUrl + encodeURIComponent(type), function (data) {
            $.each(data, function (id, name) {
                $sel.append($('<option></option>').attr('value', id).text(name));
            });
            if (selectedId) {
                $sel.val(String(selectedId));
            }
            if (typeof done === 'function') done();
        });
    }

    function loadForms(riceNameId, riceType, selectedId, done) {
        var $sel = $('#filter_form_id');
        $sel.empty().append('<option value="">— All —</option>');
        if (!riceNameId) {
            if (typeof done === 'function') done();
            return;
        }
        $.get(formsUrl, { rice_name_id: riceNameId, rice_type: riceType || '' }, function (data) {
            $.each(data, function (id, name) {
                $sel.append($('<option></option>').attr('value', id).text(name));
            });
            if (selectedId) {
                $sel.val(String(selectedId));
            }
            if (typeof done === 'function') done();
        });
    }

    function loadWands(riceNameId, formId, selectedId, done) {
        var $sel = $('#filter_grade');
        $sel.empty().append('<option value="">— All —</option>');
        if (!riceNameId || !formId) {
            if (typeof done === 'function') done();
            return;
        }
        $.get(wandsUrl, { rice_name_id: riceNameId, form_id: formId }, function (data) {
            $.each(data, function (id, label) {
                $sel.append($('<option></option>').attr('value', id).text(label));
            });
            if (selectedId) {
                $sel.val(String(selectedId));
            }
            if (typeof done === 'function') done();
        });
    }

    // Initial population from query string
    if (initialRiceType) {
        loadRiceNames(initialRiceType, initialRiceNameId, function () {
            if (initialRiceNameId) {
                loadForms(initialRiceNameId, initialRiceType, initialFormId, function () {
                    if (initialFormId) {
                        loadWands(initialRiceNameId, initialFormId, initialGrade);
                    }
                });
            }
        });
    } else if (initialRiceNameId && riceNameMeta) {
        $('#filter_rice_name_id').empty().append('<option value="">— All —</option>')
            .append($('<option></option>').attr('value', riceNameMeta.id).text(riceNameMeta.name).prop('selected', true));
        loadForms(initialRiceNameId, '', initialFormId, function () {
            if (initialFormId) {
                loadWands(initialRiceNameId, initialFormId, initialGrade);
            }
        });
    }

    $('#filter_rice_type').on('change', function () {
        $('#filter_form_id').empty().append('<option value="">— All —</option>');
        $('#filter_grade').empty().append('<option value="">— All —</option>');
        loadRiceNames($(this).val(), null, null);
    });

    $('#filter_rice_name_id').on('change', function () {
        $('#filter_grade').empty().append('<option value="">— All —</option>');
        var type = $('#filter_rice_type').val();
        loadForms($(this).val(), type, null, null);
    });

    $('#filter_form_id').on('change', function () {
        var riceNameId = $('#filter_rice_name_id').val();
        loadWands(riceNameId, $(this).val(), null, null);
    });
});
</script>
@endsection
