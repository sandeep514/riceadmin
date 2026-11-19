@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Plan Page
            <small>Visited plans without buy / trial</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="javascript:void(0)"><i class="fa fa-dashboard"></i> Home</a></li>
            <li>Activity Logs</li>
            <li class="active">Plan Page</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">Users who left on the plan page</h3>
                    </div>
                    <div style="padding:10px;">
                        <form method="GET" action="{{ route('activity.plan-page') }}" class="form-inline" style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;">
                            <div class="form-group">
                                <label for="period" style="margin-right:5px;">Period</label>
                                <select name="period" id="period" class="form-control" onchange="this.form.submit()">
                                    @foreach($periods as $key => $label)
                                        <option value="{{ $key }}" {{ ($period ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm">Apply</button>
                        </form>
                        <p class="text-muted" style="margin-top:8px;margin-bottom:0;">
                            Opened plans since {{ $from->timezone('Asia/Kolkata')->format('d M Y H:i') }} IST
                            and never bought or activated trial ({{ $users->count() }} users).
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
                                        <th style="text-align:center;">Role</th>
                                        <th style="text-align:center;">Category</th>
                                        <th style="text-align:center;">Visits</th>
                                        <th style="text-align:center;">Last Plan Visit (IST)</th>
                                        <th style="text-align:center;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($users as $user)
                                        <tr>
                                            <td style="text-align:center;">{{ $user->name ?? '--' }}</td>
                                            <td style="text-align:center;">{{ $user->email ?? '--' }}</td>
                                            <td style="text-align:center;">{{ $user->phone ?: ($user->mobile ?? '--') }}</td>
                                            <td style="text-align:center;">{{ $user->role_rel->role_name ?? '--' }}</td>
                                            <td style="text-align:center;">
                                                {{ $user->getWebBusinessDetails->getCategoryDetails->category ?? '--' }}
                                            </td>
                                            <td style="text-align:center;">{{ $user->visit_count ?? 0 }}</td>
                                            <td style="text-align:center;">
                                                @if(!empty($user->last_visited_at))
                                                    {{ \Illuminate\Support\Carbon::parse($user->last_visited_at)->timezone('Asia/Kolkata')->format('d-m-Y H:i') }}
                                                @else
                                                    --
                                                @endif
                                            </td>
                                            <td style="text-align:center;">
                                                <a href="{{ route('view.user', $user->id) }}" class="btn btn-info btn-xs">View</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted">
                                                No plan-page abandonments in this period.
                                                Visits are recorded when the portal plans APIs are called after login.
                                            </td>
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
