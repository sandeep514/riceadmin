@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Domestic Freight Charges
            <small>{{ $vendor->name ?: ('User #'.$vendor->id) }}</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="javascript:void(0)"><i class="fa fa-dashboard"></i> Home</a></li>
            <li>Domestic Vendor</li>
            <li><a href="{{ route('get.web.domestic.freight.vendors.list') }}">Vendors</a></li>
            <li class="active">Charges</li>
        </ol>
    </section>

    <section class="content">
        <div class="row" style="margin-bottom:10px;">
            <div class="col-md-12">
                <a href="{{ route('get.web.domestic.freight.vendors.list') }}" class="btn btn-default btn-sm">
                    <i class="fa fa-arrow-left"></i> Back to vendors
                </a>
                <span class="text-muted" style="margin-left:10px;">
                    {{ $vendor->name ?: ('User #'.$vendor->id) }}
                    @if($vendor->email)
                        <small>&lt;{{ $vendor->email }}&gt;</small>
                    @endif
                    &middot; {{ $charges->count() }} charge(s)
                </span>
            </div>
        </div>

        <div class="box">
            <div class="box-header">
                <h3 class="box-title">Freight charges added by this vendor</h3>
            </div>
            <div class="box-body table-responsive">
                <table class="table table-striped table-bordered" id="domesticFreightChargesTable" width="100%">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Route</th>
                        <th>Truck Size</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Updated</th>
                        <th width="200">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($charges as $charge)
                        <tr>
                            <td>{{ $charge->id }}</td>
                            <td>
                                {{ optional($charge->cityRel)->name ?: ($charge->city ?: '—') }}
                                <br><small>
                                    {{ optional($charge->destinationRel)->name ?: ($charge->destination ?: '—') }}
                                </small>
                                @if($charge->state ?: optional($charge->stateRel)->name)
                                    <br><small class="text-muted">{{ $charge->state ?: optional($charge->stateRel)->name }}</small>
                                @endif
                            </td>
                            <td>{{ $charge->truck_size ?: (optional($charge->truckSizeRel)->displayLabel() ?: '—') }}</td>
                            <td>{{ $charge->price !== null && $charge->price !== '' ? $charge->price : '—' }}</td>
                            <td>
                                @include('components.vendor-product-status-label', ['product' => $charge])
                                @if(!empty($charge->admin_message))
                                    <br><small class="text-muted">{{ \Illuminate\Support\Str::limit($charge->admin_message, 80) }}</small>
                                @endif
                            </td>
                            <td>{{ optional($charge->updated_at)->format('d-m-Y H:i') }}</td>
                            <td style="white-space:nowrap;">
                                @include('components.vendor-product-status-actions', [
                                    'product' => $charge,
                                    'route' => 'toggle.web.domestic.freight.charges.status',
                                    'kind' => null,
                                ])
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">No charges added by this vendor yet.</td>
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
        $('#domesticFreightChargesTable').DataTable({
            pageLength: 25,
            order: [[0, 'desc']],
            columnDefs: [{ orderable: false, targets: [6] }]
        });
    });
</script>
@include('components.vendor-product-deactivate-modal')
@endsection
