@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Domestic Freight Vendors
            <small>List</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="javascript:void(0)"><i class="fa fa-dashboard"></i> Home</a></li>
            <li>Domestic Vendor</li>
            <li class="active">Vendors</li>
        </ol>
    </section>

    <section class="content">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title">Vendors registered as domestic transporters / freight service providers</h3>
            </div>
            <div class="box-body table-responsive">
                <table class="table table-striped table-bordered" id="domesticFreightVendorsTable" width="100%">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Vendor</th>
                        <th>Company</th>
                        <th>Category</th>
                        <th>Product</th>
                        <th>Contact</th>
                        <th>Listing</th>
                        <th>SNTC Recommended</th>
                        <th>Status</th>
                        <th width="120">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($vendors as $vendor)
                        @php
                            $business = $vendor->getWebBusinessDetails;
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
                            <td>{{ optional($business)->product ?: '—' }}</td>
                            <td>
                                {{ optional($business)->contactPerson ?: '—' }}
                                @if(optional($business)->contactMobile)
                                    <br><small>{{ $business->contactMobile }}</small>
                                @elseif($vendor->mobile)
                                    <br><small>{{ $vendor->mobile }}</small>
                                @endif
                            </td>
                            <td>
                                @if($business)
                                    <x-active-listing-yes-no
                                        :user-id="$vendor->id"
                                        :value="$business->is_active_listing"
                                    />
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @if($business)
                                    <x-sntc-recommended-yes-no
                                        :user-id="$vendor->id"
                                        :value="$business->is_sntc_recommended"
                                    />
                                @else
                                    —
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
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted">No domestic freight vendors found.</td>
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
        $('#domesticFreightVendorsTable').DataTable({
            pageLength: 25,
            order: [[0, 'desc']],
            columnDefs: [{ orderable: false, targets: [6, 7, 9] }]
        });
    });
</script>
@endsection
