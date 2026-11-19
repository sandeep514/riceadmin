@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            New Users
            <small>Web + App</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="javascript:void(0)"><i class="fa fa-dashboard"></i> Home</a></li>
            <li>Activity Logs</li>
            <li class="active">New Users</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">Newly registered users</h3>
                    </div>
                    <div style="padding:10px;">
                        <form method="GET" action="{{ route('activity.new-users') }}" class="form-inline" style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;">
                            <div class="form-group">
                                <label for="from" style="margin-right:5px;">From</label>
                                <input type="date" id="from" name="from" class="form-control" value="{{ $from ?? '' }}">
                            </div>
                            <div class="form-group">
                                <label for="to" style="margin-right:5px;">To</label>
                                <input type="date" id="to" name="to" class="form-control" value="{{ $to ?? '' }}">
                            </div>
                            <div class="form-group">
                                <label for="source" style="margin-right:5px;">Source</label>
                                <select name="source" id="source" class="form-control">
                                    @foreach($sources as $key => $label)
                                        <option value="{{ $key }}" {{ ($source ?? 'all') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="role" style="margin-right:5px;">Role</label>
                                <select name="role" id="role" class="form-control">
                                    <option value="">All roles</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" {{ (string) ($roleId ?? '') === (string) $role->id ? 'selected' : '' }}>
                                            {{ $role->role_name }}@if(!empty($role->type)) ({{ $role->type }})@endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="category" style="margin-right:5px;">Category</label>
                                <select name="category" id="category" class="form-control">
                                    <option value="">All categories</option>
                                    <optgroup label="Web categories">
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ (string) ($categoryId ?? '') === (string) $category->id ? 'selected' : '' }}>
                                                {{ $category->category }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                    @if(($bagCategories ?? collect())->isNotEmpty())
                                        <optgroup label="App bag categories">
                                            @foreach($bagCategories as $category)
                                                <option value="{{ $category->id }}" {{ (string) ($categoryId ?? '') === (string) $category->id ? 'selected' : '' }}>
                                                    {{ $category->category }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endif
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm">Apply</button>
                            <a href="{{ route('activity.new-users') }}" class="btn btn-default btn-sm">Clear</a>
                        </form>
                        <p class="text-muted" style="margin-top:8px;margin-bottom:0;">
                            {{ $users->count() }} users matched.
                        </p>
                    </div>
                    <div class="box-body">
                        <div class="table-responsive">
                            <table id="example2" class="display" style="width:100%;">
                                <thead>
                                    <tr>
                                        <th style="text-align:center;">Name</th>
                                        <th style="text-align:center;">Email</th>
                                        <th style="text-align:center;">Phone</th>
                                        <th style="text-align:center;">Source</th>
                                        <th style="text-align:center;">Joined (IST)</th>
                                        <th style="text-align:center;">Role</th>
                                        <th style="text-align:center;">Category</th>
                                        <th style="text-align:center;">Status</th>
                                        <th style="text-align:center;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($users as $user)
                                        <tr>
                                            <td style="text-align:center;">{{ $user->name ?? '--' }}</td>
                                            <td style="text-align:center;">{{ $user->email ?? '--' }}</td>
                                            <td style="text-align:center;">{{ $user->phone ?: ($user->mobile ?? '--') }}</td>
                                            <td style="text-align:center;">@include('activity_logs._source_badge', ['user' => $user])</td>
                                            <td style="text-align:center;">
                                                {{ $user->created_at ? \Illuminate\Support\Carbon::parse($user->created_at)->timezone('Asia/Kolkata')->format('d-m-Y H:i') : '--' }}
                                            </td>
                                            <td style="text-align:center;">
                                                {{ $user->role_rel->role_name ?? ($user->role_rel_usd->role_name ?? '--') }}
                                            </td>
                                            <td style="text-align:center;">
                                                {{ $user->getWebBusinessDetails->getCategoryDetails->category
                                                    ?? ($user->bagVendor->category ?? '--') }}
                                            </td>
                                            <td style="text-align:center;">
                                                @if((int) ($user->is_deactivated ?? 0) === 1 && (int) ($user->is_active_by_admin ?? 0) === 0)
                                                    <span class="label label-danger">Rejected</span>
                                                @elseif((int) ($user->is_deactivated ?? 0) === 1)
                                                    <span class="label label-danger">Deactivated</span>
                                                @elseif((int) ($user->is_active_by_admin ?? 0) === 1 || (int) ($user->status ?? 0) === 1)
                                                    <span class="label label-success">Active</span>
                                                @else
                                                    <span class="label label-warning">Pending</span>
                                                @endif
                                            </td>
                                            <td style="text-align:center;">
                                                <a href="{{ route('view.user', $user->id) }}" class="btn btn-info btn-xs">View</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center text-muted">No users for the selected filters.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
