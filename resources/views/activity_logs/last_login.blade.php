@extends('layouts.main')

@section('content')
@php
    $activityColumn = $activityColumn ?? 'last_active_at';
@endphp
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Recently Active Users
            <small>Used website / app recently</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="javascript:void(0)"><i class="fa fa-dashboard"></i> Home</a></li>
            <li>Activity Logs</li>
            <li class="active">Recently Active Users</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">Users who used the app recently</h3>
                    </div>
                    <div style="padding:10px;">
                        <form method="GET" action="{{ route('activity.last-login-users') }}" class="form-inline" style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;">
                            <div class="form-group">
                                <label for="period" style="margin-right:5px;">Period</label>
                                <select name="period" id="period" class="form-control">
                                    @foreach($periods as $key => $label)
                                        <option value="{{ $key }}" {{ ($period ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="source" style="margin-right:5px;">Source</label>
                                <select name="source" id="source" class="form-control">
                                    @foreach($sources as $key => $label)
                                        <option value="{{ $key }}" {{ ($source ?? 'all') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm">Apply</button>
                        </form>
                        <p class="text-muted" style="margin-top:8px;margin-bottom:0;">
                            Active since {{ $from->timezone('Asia/Kolkata')->format('d M Y H:i') }} IST
                            ({{ $users->count() }} users). Updated when they call authenticated APIs (throttled).
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
                                        <th style="text-align:center;">Role</th>
                                        <th style="text-align:center;">Category</th>
                                        <th style="text-align:center;">Platform</th>
                                        <th style="text-align:center;">Last Active (IST)</th>
                                        <th style="text-align:center;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($users as $user)
                                        @php
                                            $activeAt = $user->last_active_at ?: $user->last_login_at;
                                            $activePlatform = $user->last_active_platform ?: ($user->last_login_platform ?? null);
                                        @endphp
                                        <tr>
                                            <td style="text-align:center;">{{ $user->name ?? '--' }}</td>
                                            <td style="text-align:center;">{{ $user->email ?? '--' }}</td>
                                            <td style="text-align:center;">{{ $user->phone ?: ($user->mobile ?? '--') }}</td>
                                            <td style="text-align:center;">@include('activity_logs._source_badge', ['user' => $user])</td>
                                            <td style="text-align:center;">
                                                {{ $user->role_rel->role_name ?? ($user->role_rel_usd->role_name ?? '--') }}
                                            </td>
                                            <td style="text-align:center;">
                                                {{ $user->getWebBusinessDetails->getCategoryDetails->category
                                                    ?? ($user->bagVendor->category ?? '--') }}
                                            </td>
                                            <td style="text-align:center;">{{ $activePlatform ?: '--' }}</td>
                                            <td style="text-align:center;">
                                                {{ $activeAt ? \Illuminate\Support\Carbon::parse($activeAt)->timezone('Asia/Kolkata')->format('d-m-Y H:i') : '--' }}
                                            </td>
                                            <td style="text-align:center;">
                                                <a href="{{ route('view.user', $user->id) }}" class="btn btn-info btn-xs">View</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center text-muted">No recent activity in this period.</td>
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
