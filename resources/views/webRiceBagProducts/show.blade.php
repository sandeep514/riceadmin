@extends('layouts.main')

@section('content')
<style>
    .rice-bag-size-img {
        max-width: 100px;
        max-height: 100px;
        object-fit: contain;
        border: 1px solid #eee;
        border-radius: 4px;
        background: #fff;
        padding: 4px;
    }
    .rice-bag-detail-table th {
        width: 200px;
        background: #f9f9f9;
    }
</style>
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Rice Bag Product
            <small>Review</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="{{ route('get.web.rice.bag.products.list') }}">Rice Bag Products</a></li>
            <li class="active">View</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12" style="margin-bottom:10px;">
                <a href="{{ route('get.web.rice.bag.products.list') }}" class="btn btn-default btn-sm">
                    <i class="fa fa-arrow-left"></i> Back to list
                </a>
                <a class="btn btn-sm btn-{{ (int) $product->status === 0 ? 'success' : 'danger' }}"
                   href="{{ route('toggle.web.rice.bag.products.status', $product->id) }}"
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
                        <table class="table table-bordered rice-bag-detail-table">
                            <tr>
                                <th>ID</th>
                                <td>{{ $product->id }}</td>
                            </tr>
                            <tr>
                                <th>Bag type</th>
                                <td>{{ $bagTypes[$product->bag_type_id] ?? ($product->bag_type_id ? '#'.$product->bag_type_id : '—') }}</td>
                            </tr>
                            <tr>
                                <th>Packing form</th>
                                <td>{{ $product->packing_form ?? '—' }} @if($product->packing_form_id)(ID: {{ $product->packing_form_id }})@endif</td>
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
                                        @if(!empty($product->user->mobile))
                                            <br><small>{{ $product->user->mobile }}</small>
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
                            <tr>
                                <th>Last updated</th>
                                <td>{{ $product->updated_at ? \Carbon\Carbon::parse($product->updated_at)->format('d-m-Y H:i') : '—' }}</td>
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
                        <h3 class="box-title">Packing sizes ({{ $product->packingSizes->count() }})</h3>
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
                                <th>Bag size</th>
                                <th>Bag weight</th>
                                <th>Image</th>
                            </tr>
                            </thead>
                            <tbody>
                                @forelse($product->packingSizes as $size)
                                    <tr>
                                        <td>{{ $size->id }}</td>
                                        <td>
                                            {{ $size->packing_size ?? '—' }}
                                            @if($size->packing_size_id)
                                                <br><small class="text-muted">ID: {{ $size->packing_size_id }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $size->rate ?? '—' }}</td>
                                        <td>{{ $size->gst ?? '—' }}</td>
                                        <td>{{ $size->total_price ?? '—' }}</td>
                                        <td>{{ $size->bag_size ?? '—' }}</td>
                                        <td>{{ $size->bag_weight ?? '—' }}</td>
                                        <td>
                                            @if(!empty($size->image))
                                                <a href="{{ asset($imageBasePath.'/'.$size->image) }}" target="_blank" rel="noopener">
                                                    <img src="{{ asset($imageBasePath.'/'.$size->image) }}"
                                                         alt="packing size"
                                                         class="rice-bag-size-img"
                                                         onerror="this.style.display='none';">
                                                </a>
                                                <div style="font-size:10px;color:#999;word-break:break-all;">{{ $size->image }}</div>
                                            @else
                                                —
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">No packing sizes</td>
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
