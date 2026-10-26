@extends('layouts.main')
@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                Web Users
                <small>List</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="javascript:void(0)"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="javascript:void(0)"><i class="fa fa-dashboard"></i> Users</a></li>
                <li class="active">Dashboard</li>
            </ol>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-xs-12">
                    <div class="box">
                        <div class="box-header">
                            <h3 class="box-title">List of web users</h3>
                        </div>
                        <div style="padding: 10px">
                            <form method="GET" action="{{ route('web.user') }}" class="form-inline" style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;">
                                <div class="form-group">
                                    <label for="from" style="margin-right:5px">Joined From</label>
                                    <input type="date" id="from" name="from" class="form-control" value="{{ $from ?? '' }}">
                                </div>
                                <div class="form-group">
                                    <label for="to" style="margin-right:5px">Joined To</label>
                                    <input type="date" id="to" name="to" class="form-control" value="{{ $to ?? '' }}">
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm">Apply</button>
                                <a href="{{ route('web.user') }}" class="btn btn-default btn-sm">Clear</a>
                                <a href="{{ request()->fullUrlWithQuery(['from' => $from ?? '', 'to' => $to ?? '', 'export' => 'csv']) }}" class="btn btn-success btn-sm">
                                    <i class="fa fa-download"></i> Export CSV (Full)
                                </a>
                            </form>
                        </div>
                        <a href="{{ route('mark.as.viewed') }}" class="btn btn-success btn-sm">Mark as viewed</a>
                        <!-- /.box-header -->
                        <div class="box-body">
                            <div class="table-responsive">
                                <table id="example2" class="display" style="width: 100%;">
                                    <thead>
                                        <tr>
                                            <th style="text-align: center">Name</th>
                                            <th style="text-align: center">email </th>
                                            <th style="text-align: center">Phone </th>
                                            <th style="text-align: center">Joined</th>
                                            <th style="text-align: center">Category </th>
                                            <th style="text-align: center">Onboarding Stage</th>
                                            <th style="text-align: center">SNTC Recommended</th>
                                            <th style="text-align: center">Status </th>
                                            <th style="text-align: center">Action </th>
                                            <!-- <th style="text-align: center">country</th> -->
                                            <!-- <th style="text-align: center">Bag Vendor</th> -->
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @foreach($vendorUsers as $k => $v)
                                            <tr>
                                                <td style="text-align: center">
                                                    @if( $v->is_viewed_by_admin == 0 )
                                                        <img src="{{ asset('new-icon.png') }}" style="width: 45px">
                                                    @endif
                                                    
                                                    {{ $v->name ?? '--' }}</td>
                                                <td style="text-align: center">{{ $v->email ?? '--' }}</td>
                                                <td style="text-align: center">{{ $v->phone ?: $v->mobile }}</td>
                                                <td style="text-align: center">{{ $v->created_at ? \Illuminate\Support\Carbon::parse($v->created_at)->format('d-m-Y') : '--' }}</td>

                                                <td style="text-align: center">{{ ($v->getWebBusinessDetails != null)?  ( $v->getWebBusinessDetails->getCategoryDetails )? ($v->getWebBusinessDetails->getCategoryDetails->category) : '--' : '--' }}</td>
                                                <td style="text-align: center">
                                                    @php
                                                        $stageLabel = $v->onboarding_stage ?? '';
                                                        $stageClass = str_starts_with($stageLabel, 'Paid plan') ? 'label-success'
                                                            : ($stageLabel === 'Trial started' ? 'label-info'
                                                            : ($stageLabel === 'Documents uploaded' ? 'label-primary'
                                                            : ($stageLabel === 'Profile completed' ? 'label-warning' : 'label-danger')));
                                                    @endphp
                                                    <span class="label {{ $stageClass }}">{{ $stageLabel }}</span>
                                                </td>
                                                <td style="text-align: center">
                                                    @if($v->getWebBusinessDetails)
                                                        <x-sntc-recommended-yes-no
                                                            :user-id="$v->id"
                                                            :value="$v->getWebBusinessDetails->is_sntc_recommended"
                                                        />
                                                    @else
                                                        —
                                                    @endif
                                                </td>
                                                <td style="text-align: center">
                                                    @if((int) ($v->is_deactivated ?? 0) === 1)
                                                        <span class="label label-danger">Deactivated</span>
                                                    @elseif((int) ($v->is_active_by_admin ?? 0) === 1)
                                                        <span class="label label-success">Active</span>
                                                    @else
                                                        <span class="label label-warning">Pending</span>
                                                    @endif
                                                </td>
                                                <td style="text-align: center">
                                                    <a href="{{ route('view.user', $v->id) }}" class="btn btn-info btn-xs">View</a>
                                                    @php
                                                        $hasPlan = $v->getWebUserSubscription !== null;
                                                    @endphp
                                                    @if((int) ($v->is_active_by_admin ?? 0) === 0)
                                                        @if($hasPlan)
                                                            <a href="{{ route('list.web.change.status.user', $v->id) }}" class="btn btn-success btn-xs" onclick="return confirm('Activate this user?');">Activate</a>
                                                        @endif
                                                    @elseif((int) ($v->is_deactivated ?? 0) === 1)
                                                        <a href="{{ route('list.web.change.status.user', $v->id) }}" class="btn btn-success btn-xs" onclick="return confirm('Re-activate this user?');">Re-Activate</a>
                                                    @else
                                                        <a href="{{ route('list.web.change.status.user', $v->id) }}" class="btn btn-danger btn-xs" onclick="return confirm('De-activate this user?');">De-Activate</a>
                                                    @endif
                                                </td>
                                                <!-- <td style="text-align: center">{{ $v->country  }}</td> -->
                                                <!-- {{-- <td style="text-align: center">{{ $v->bagVendor->name  }}</td> --}} -->
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    
                                    <tfoot>
                                        <tr>
                                            <th style="text-align: center">Name</th>
                                            <th style="text-align: center">email </th>
                                            <th style="text-align: center">Phone </th>
                                            <th style="text-align: center">Joined</th>
                                            <th style="text-align: center">Category </th>
                                            <th style="text-align: center">Onboarding Stage</th>
                                            <th style="text-align: center">SNTC Recommended</th>
                                            <th style="text-align: center">Status </th>
                                            <th style="text-align: center">Action </th>
                                            <!-- <th style="text-align: center">country</th> -->
                                            <!-- <th style="text-align: center">Bag Vendor</th> -->
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
@section('scripts')
@endsection