@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Notify Web User
            <small>Broadcast</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="{{ route('home') }}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Notify Web User</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-10">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Send notification</h3>
                    </div>
                    <form action="{{ route('notify.web.user.store') }}" method="POST" id="notify-web-user-form">
                        @csrf
                        <div class="box-body">
                            <div class="form-group col-md-6">
                                <label>Date</label>
                                <input type="date" name="notify_date" class="form-control" value="{{ old('notify_date', date('Y-m-d')) }}" autocomplete="off" required>
                            </div>
                            <div class="clearfix"></div>
                            <div class="form-group col-md-12">
                                <label>Title <span class="text-danger">*</span></label>
                                <input type="text" name="title" class="form-control" value="{{ old('title') }}" maxlength="500" required>
                            </div>
                            <div class="form-group col-md-12">
                                <label>Message <span class="text-danger">*</span></label>
                                <textarea name="message" class="form-control" rows="4" required>{{ old('message') }}</textarea>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Role <span class="text-danger">*</span></label>
                                <select name="role_id" id="notify_role_id" class="form-control select2" required>
                                    <option value="">-- Select role --</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" {{ (string) old('role_id') === (string) $role->id ? 'selected' : '' }}>{{ $role->role_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Category <span class="text-danger">*</span></label>
                                <select name="category_id" id="notify_category_id" class="form-control select2" required>
                                    <option value="">-- Select category --</option>
                                </select>
                            </div>
                            <div class="clearfix"></div>
                            <div class="form-group col-md-12">
                                <label>Recipients</label><br>
                                <label class="radio-inline">
                                    <input type="radio" name="audience_mode" value="all_category" class="audience-mode" {{ old('audience_mode', 'all_category') === 'all_category' ? 'checked' : '' }}> All users in this role &amp; category
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="audience_mode" value="selected_users" class="audience-mode" {{ old('audience_mode') === 'selected_users' ? 'checked' : '' }}> Selected users
                                </label>
                            </div>
                            <div class="form-group col-md-12" id="user-multiselect-wrap" style="display:none;">
                                <label>Users</label>
                                <select name="user_ids[]" id="notify_user_ids" class="form-control select2" multiple="multiple" style="width:100%;" data-placeholder="Select users"></select>
                                <p class="help-block">Choose one or more users (must belong to the selected role and category).</p>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary">Send notification</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">Recent broadcasts</h3>
                    </div>
                    <div class="box-body table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Title</th>
                                    <th>Recipients</th>
                                    <th>Audience</th>
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
                                        <td>{{ $row->audience_mode === 'selected_users' ? 'Selected users' : 'All in category' }}</td>
                                        <td>{{ $row->created_at ? \Carbon\Carbon::parse($row->created_at)->format('d-m-Y H:i') : '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No notifications sent yet.</td>
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
(function () {
    var base = window.route || '{{ url('/administrator') }}';

    function loadCategories(roleId) {
        var $cat = $('#notify_category_id');
        $cat.empty().append('<option value="">-- Select category --</option>');
        if (!roleId) return;
        $.getJSON(base + '/notify/web-user/categories/' + roleId, function (res) {
            if (!res || !res.data) return;
            res.data.forEach(function (c) {
                $cat.append('<option value="' + c.id + '">' + $('<div>').text(c.category).html() + '</option>');
            });
            $cat.trigger('change');
        });
    }

    function loadUsers() {
        var roleId = $('#notify_role_id').val();
        var categoryId = $('#notify_category_id').val();
        var $users = $('#notify_user_ids');
        $users.empty();
        if (!roleId || !categoryId) return;
        $.getJSON(base + '/notify/web-user/users', { role_id: roleId, category_id: categoryId }, function (res) {
            if (!res || !res.data) return;
            res.data.forEach(function (u) {
                var label = (u.name || '') + ' — ' + (u.mobile || '') + ' (ID ' + u.id + ')';
                $users.append('<option value="' + u.id + '">' + $('<div>').text(label).html() + '</option>');
            });
            $users.trigger('change');
        });
    }

    function toggleAudience() {
        var mode = $('.audience-mode:checked').val();
        if (mode === 'selected_users') {
            $('#user-multiselect-wrap').show();
            $('#notify_user_ids').prop('required', true);
            loadUsers();
        } else {
            $('#user-multiselect-wrap').hide();
            $('#notify_user_ids').prop('required', false);
        }
    }

    $('#notify_role_id').on('change', function () {
        loadCategories($(this).val());
    });

    $('#notify_category_id').on('change', function () {
        if ($('.audience-mode:checked').val() === 'selected_users') {
            loadUsers();
        }
    });

    $('.audience-mode').on('change', toggleAudience);

    @if(old('role_id'))
        loadCategories('{{ old('role_id') }}');
        setTimeout(function () {
            $('#notify_category_id').val('{{ old('category_id') }}').trigger('change');
            toggleAudience();
        }, 800);
    @endif

    toggleAudience();
})();
</script>
@endsection
