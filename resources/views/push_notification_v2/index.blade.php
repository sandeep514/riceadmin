@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Push Notification new version
            <small>Web socket + Firebase</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('home') }}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Push Notification new version</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-10">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Send notification</h3>
                    </div>
                    <form action="{{ route('push.notification.v2.store') }}" method="POST" id="push-notification-v2-form">
                        @csrf
                        <div class="box-body">
                            <p class="help-block" style="margin-top:0;">
                                Sends <strong>Reverb/Pusher</strong> to matching web portal users and <strong>Firebase</strong>
                                to matching app users (and web users with an app token).
                                Recipients are matched by <strong>role</strong> and selected <strong>business categories</strong>.
                            </p>

                            <div class="form-group col-md-6" style="padding-left:0;">
                                <label>Role <span class="text-danger">*</span></label>
                                <select name="role_id" id="push_v2_role_id" class="form-control select2" required>
                                    <option value="">-- Select role --</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" {{ (string) old('role_id') === (string) $role->id ? 'selected' : '' }}>
                                            {{ $role->role_name }}@if(!empty($role->type)) ({{ $role->type }})@endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('role_id')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="clearfix"></div>

                            <div class="form-group col-md-12" style="padding-left:0;">
                                <label>Categories <span class="text-danger">*</span></label>
                                <p class="help-block" style="margin-top:0;font-size:12px;">
                                    Categories update when you change the role. Select one or more.
                                    <label style="font-weight:normal;margin-left:10px;display:inline;white-space:nowrap;">
                                        <input type="checkbox" id="push-v2-categories-select-all" title="Select or clear all"> All
                                    </label>
                                </p>
                                <div id="push-v2-categories-grid" class="row" style="clear:both;max-height:260px;overflow-y:auto;border:1px solid #ddd;border-radius:4px;padding:10px 6px;background:#fafafa;margin-left:0;margin-right:0;">
                                    @php $oldCats = array_map('intval', (array) old('category_ids', [])); @endphp
                                    @if(($categories ?? collect())->isEmpty())
                                        <div class="col-md-12 text-muted push-v2-empty" style="padding:8px;">Select a role to load categories.</div>
                                    @else
                                        @foreach($categories as $cat)
                                            <div class="col-md-3 col-sm-4 col-xs-6" style="margin-bottom:8px;">
                                                <label style="font-weight:normal;margin-bottom:0;">
                                                    <input type="checkbox" name="category_ids[]" value="{{ $cat->id }}"
                                                        class="push-v2-category"
                                                        {{ in_array((int) $cat->id, $oldCats, true) ? 'checked' : '' }}>
                                                    {{ $cat->category }}
                                                </label>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                                @error('category_ids')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="clearfix"></div>

                            <div class="form-group col-md-12" style="padding-left:0;">
                                <label>Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" value="{{ old('title') }}" maxlength="500" required>
                                @error('title')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group col-md-12" style="padding-left:0;">
                                <label>Message <span class="text-danger">*</span></label>
                                <textarea name="message" class="form-control" rows="5" required>{{ old('message') }}</textarea>
                                @error('message')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-paper-plane"></i> Send notification
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">Recent broadcasts (this screen)</h3>
                    </div>
                    <div class="box-body table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Title</th>
                                    <th>Web recipients</th>
                                    <th>Sent at</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($history as $row)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $row->notify_date ? \Carbon\Carbon::parse($row->notify_date)->format('d-m-Y') : '-' }}</td>
                                        <td>{{ \Illuminate\Support\Str::limit($row->title, 80) }}</td>
                                        <td>{{ $recipientCounts[$row->broadcast_group_id] ?? 1 }}</td>
                                        <td>{{ $row->created_at ? \Carbon\Carbon::parse($row->created_at)->format('d-m-Y H:i') : '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No notifications sent from this screen yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@section('javascript')
<script>
$(function () {
    var base = window.route || '{{ url('/administrator') }}';
    var oldCategoryIds = @json(array_map('intval', (array) old('category_ids', [])));

    if ($.fn.select2) {
        $('#push_v2_role_id').select2({ width: '100%' });
    }

    function escapeHtml(text) {
        return $('<div>').text(text == null ? '' : String(text)).html();
    }

    function renderCategories(list, selectedIds) {
        selectedIds = selectedIds || [];
        var $grid = $('#push-v2-categories-grid');
        $grid.empty();
        $('#push-v2-categories-select-all').prop('checked', false);
        if ($.fn.iCheck) {
            $('#push-v2-categories-select-all').iCheck('update');
        }

        if (!list || !list.length) {
            $grid.append('<div class="col-md-12 text-muted push-v2-empty" style="padding:8px;">No categories found for this role.</div>');
            return;
        }

        list.forEach(function (c) {
            var checked = selectedIds.indexOf(parseInt(c.id, 10)) !== -1 ? ' checked' : '';
            $grid.append(
                '<div class="col-md-3 col-sm-4 col-xs-6" style="margin-bottom:8px;">' +
                    '<label style="font-weight:normal;margin-bottom:0;">' +
                        '<input type="checkbox" name="category_ids[]" value="' + c.id + '" class="push-v2-category"' + checked + '> ' +
                        escapeHtml(c.category) +
                    '</label>' +
                '</div>'
            );
        });

        if ($.fn.iCheck) {
            $grid.find('input.push-v2-category').iCheck({
                checkboxClass: 'icheckbox_square-blue',
                radioClass: 'iradio_square-blue'
            });
        }
    }

    function loadCategories(roleId, selectedIds) {
        if (!roleId) {
            $('#push-v2-categories-grid').html(
                '<div class="col-md-12 text-muted push-v2-empty" style="padding:8px;">Select a role to load categories.</div>'
            );
            $('#push-v2-categories-select-all').prop('checked', false);
            return;
        }

        $('#push-v2-categories-grid').html(
            '<div class="col-md-12 text-muted" style="padding:8px;">Loading categories…</div>'
        );

        $.getJSON(base + '/push/notification/v2/categories/' + roleId)
            .done(function (res) {
                renderCategories((res && res.data) ? res.data : [], selectedIds || []);
            })
            .fail(function () {
                $('#push-v2-categories-grid').html(
                    '<div class="col-md-12 text-danger" style="padding:8px;">Failed to load categories.</div>'
                );
            });
    }

    function setAllCategories(checked) {
        var $boxes = $('#push-v2-categories-grid input.push-v2-category');
        $boxes.prop('checked', checked);
        if ($.fn.iCheck) {
            $boxes.iCheck(checked ? 'check' : 'uncheck');
        }
    }

    $('#push_v2_role_id').on('change', function () {
        loadCategories($(this).val(), []);
    });

    $('#push-v2-categories-select-all').on('change ifChanged', function () {
        setAllCategories($(this).is(':checked'));
    });

    $('#push-notification-v2-form').on('submit', function (e) {
        if ($('#push-v2-categories-grid input.push-v2-category:checked').length === 0) {
            e.preventDefault();
            alert('Please select at least one category.');
            return false;
        }
    });
});
</script>
@endsection
