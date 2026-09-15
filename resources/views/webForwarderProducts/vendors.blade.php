@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Forwarder Vendors
            <small>List</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="javascript:void(0)"><i class="fa fa-dashboard"></i> Home</a></li>
            <li>Service Providers</li>
            <li class="active">Forwarder Vendors</li>
        </ol>
    </section>

    <section class="content">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title">Vendors registered as forwarders</h3>
                <div class="pull-right">
                    <a href="{{ route('get.web.forwarder.products.list') }}" class="btn btn-primary btn-sm">
                        Forwarder Products
                    </a>
                </div>
            </div>
            <div class="box-body table-responsive">
                <table class="table table-striped table-bordered" id="forwarderVendorsTable" width="100%">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Vendor</th>
                        <th>Company</th>
                        <th>Category</th>
                        <th>Contact</th>
                        <th>Products</th>
                        <th>Listing</th>
                        <th>Status</th>
                        <th width="180">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($vendors as $vendor)
                        @php
                            $business = $vendor->getWebBusinessDetails;
                            $counts = $productCounts[$vendor->id] ?? null;
                        @endphp
                        <tr>
                            <td>{{ $vendor->id }}</td>
                            <td>
                                {{ $vendor->name ?: '—' }}
                                @if($vendor->email)
                                    <br><small>{{ $vendor->email }}</small>
                                @endif
                            </td>
                            <td>{{ optional($business)->company_name ?: '—' }}</td>
                            <td>
                                {{ optional(optional($business)->getCategoryDetails)->category
                                    ?: (optional($business)->selected_category ?: '—') }}
                            </td>
                            <td>
                                {{ optional($business)->contactPerson ?: '—' }}
                                @if(optional($business)->contactMobile)
                                    <br><small>{{ $business->contactMobile }}</small>
                                @elseif($vendor->mobile)
                                    <br><small>{{ $vendor->mobile }}</small>
                                @endif
                            </td>
                            <td>
                                {{ $counts->total ?? 0 }} total
                                <br><small>{{ $counts->verified ?? 0 }} verified</small>
                            </td>
                            <td>
                                @if((int) optional($business)->is_active_listing === 1)
                                    <span class="label label-success">Active listing</span>
                                @else
                                    <span class="label label-default">Hidden</span>
                                @endif
                            </td>
                            <td>
                                @if((int) ($vendor->is_deactivated ?? 0) === 1)
                                    <span class="label label-danger">Deactivated</span>
                                @elseif((int) ($vendor->is_active_by_admin ?? 0) === 1)
                                    <span class="label label-success">Active</span>
                                @else
                                    <span class="label label-warning">Pending</span>
                                @endif
                            </td>
                            <td style="white-space:nowrap;">
                                <a href="{{ route('view.user', $vendor->id) }}" class="btn btn-info btn-xs">View user</a>
                                <a href="{{ route('get.web.forwarder.products.list') }}" class="btn btn-primary btn-xs">Products</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted">No forwarder vendors found.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
@endsection

@section('javascript')
<script>
    $(function () {
        $('#forwarderVendorsTable').DataTable({
            pageLength: 25,
            order: [[0, 'desc']],
            columnDefs: [{ orderable: false, targets: [8] }]
        });
    });
</script>
@endsection
