@extends('layouts.main')

@section('content')
<style>
    .vendor-catalogue-img {
        max-width: 100px;
        max-height: 100px;
        object-fit: contain;
        border: 1px solid #eee;
        border-radius: 4px;
        background: #fff;
        padding: 4px;
    }
    .vendor-product-detail-table th {
        width: 200px;
        background: #f9f9f9;
    }
</style>
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Lab Equipment Product
            <small>Review</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li>Vendor Products</li>
            <li><a href="{{ route('get.web.lab.equipment.products.list') }}">Lab Equipment Products</a></li>
            <li class="active">View</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12" style="margin-bottom:10px;">
                <a href="{{ route('get.web.lab.equipment.products.list') }}" class="btn btn-default btn-sm">
                    <i class="fa fa-arrow-left"></i> Back to list
                </a>
                @include('components.vendor-product-status-actions', [
                    'product' => $product,
                    'route' => 'toggle.web.lab.equipment.products.status',
                ])
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Product details</h3>
                    </div>
                    <div class="box-body table-responsive">
                        <table class="table table-bordered vendor-product-detail-table">
                            <tr>
                                <th>ID</th>
                                <td>{{ $product->id }}</td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    @if((int) $product->status === 1)
                                        <span class="label label-success">Verified / Active</span>
                                    @else
                                        <span class="label label-warning">Pending review</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Owner</th>
                                <td>
                                    @if($product->user)
                                        {{ $product->user->name ?? '—' }} (ID: {{ $product->user_id }})
                                        @if(!empty($product->user->email))
                                            <br><small>{{ $product->user->email }}</small>
                                        @endif
                                    @else
                                        User #{{ $product->user_id }}
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Submitted</th>
                                <td>{{ $product->created_at ? \Carbon\Carbon::parse($product->created_at)->format('d-m-Y H:i') : '—' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">Equipment variants ({{ $product->variants->count() }})</h3>
                    </div>
                    <div class="box-body table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Lab equipment</th>
                                <th>Rate</th>
                                <th>Description</th>
                                <th>Catalogue</th>
                            </tr>
                            </thead>
                            <tbody>
                                @forelse($product->variants as $variant)
                                    <tr>
                                        <td>{{ $variant->id }}</td>
                                        <td>
                                            {{ $variant->equipment_name ?? '—' }}
                                            @if($variant->equipment_id)
                                                <br><small class="text-muted">ID: {{ $variant->equipment_id }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $variant->rate ?? '—' }}</td>
                                        <td>{{ $variant->description ?? '—' }}</td>
                                        <td>
                                            @if(!empty($variant->catalogue))
                                                @php $fileUrl = asset($imageBasePath.'/'.$variant->catalogue); @endphp
                                                <a href="{{ $fileUrl }}" target="_blank" rel="noopener">
                                                    <img src="{{ $fileUrl }}" alt="catalogue" class="vendor-catalogue-img" onerror="this.style.display='none';">
                                                </a>
                                                <div style="font-size:10px;color:#999;word-break:break-all;">{{ $variant->catalogue }}</div>
                                            @else
                                                —
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No variants</td>
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
@include('components.vendor-product-deactivate-modal')
@endsection
