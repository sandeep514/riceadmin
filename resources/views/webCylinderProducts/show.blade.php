@extends('layouts.main')

@section('content')
<style>
    .vendor-product-size-img {
        max-width: 100px;
        max-height: 100px;
        object-fit: contain;
        border: 1px solid #eee;
        border-radius: 4px;
        background: #fff;
        padding: 4px;
        cursor: pointer;
    }
    .vendor-product-detail-table th {
        width: 200px;
        background: #f9f9f9;
    }
</style>
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Cylinder Product
            <small>Review</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li>Vendor Products</li>
            <li><a href="{{ route('get.web.cylinder.products.list') }}">Cylinder Products</a></li>
            <li class="active">View</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12" style="margin-bottom:10px;">
                <a href="{{ route('get.web.cylinder.products.list') }}" class="btn btn-default btn-sm">
                    <i class="fa fa-arrow-left"></i> Back to list
                </a>
                <a class="btn btn-sm btn-{{ (int) $product->status === 0 ? 'success' : 'danger' }}"
                   href="{{ route('toggle.web.cylinder.products.status', $product->id) }}"
                   onclick="return confirm('{{ (int) $product->status === 0 ? 'Verify and show this product on front?' : 'Hide this product from front?' }}');">
                    {{ (int) $product->status === 0 ? 'Verify' : 'De-activate' }}
                </a>
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
                                <th>Cylinder type</th>
                                <td>
                                    {{ $types[$product->cylinder_type_id] ?? ($product->cylinder_type_id ? '#'.$product->cylinder_type_id : '—') }}
                                    @if(!empty($product->other_type_value))
                                        <br><small class="text-muted">Other: {{ $product->other_type_value }}</small>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Specification</th>
                                <td>{{ $product->specification ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th>Description</th>
                                <td>{{ $product->description ?? '—' }}</td>
                            </tr>
                            <tr>
                                <th>Additional information</th>
                                <td>{{ $product->additional_information ?? '—' }}</td>
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
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">Variants ({{ $product->variants->count() }})</h3>
                    </div>
                    <div class="box-body table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Packing size</th>
                                <th>Rate</th>
                                <th>GST</th>
                                <th>Total price</th>
                                <th>Size</th>
                                <th>Weight</th>
                                <th>Image</th>
                            </tr>
                            </thead>
                            <tbody>
                                @forelse($product->variants as $variant)
                                    <tr>
                                        <td>{{ $variant->id }}</td>
                                        <td>
                                            {{ $variant->packing_size ?? '—' }}
                                            @if(!empty($variant->other_size_value))
                                                <br><small class="text-muted">Other: {{ $variant->other_size_value }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $variant->rate ?? '—' }}</td>
                                        <td>{{ $variant->gst ?? '—' }}</td>
                                        <td>{{ $variant->total_price ?? '—' }}</td>
                                        <td>{{ $variant->bag_size ?? '—' }}</td>
                                        <td>{{ $variant->bag_weight ?? '—' }}</td>
                                        <td>
                                            @if(!empty($variant->image))
                                                @php $imgUrl = asset($imageBasePath.'/'.$variant->image); @endphp
                                                <a href="javascript:void(0);" class="vendor-product-image-preview" data-img="{{ $imgUrl }}">
                                                    <img src="{{ $imgUrl }}" alt="" class="vendor-product-size-img" onerror="this.style.display='none';">
                                                </a>
                                            @else
                                                —
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">No variants</td>
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
