@extends('layouts.main')

@section('content')
<style>
    .rice-bag-thumb {
        max-width: 56px;
        max-height: 56px;
        object-fit: contain;
        border: 1px solid #ddd;
        border-radius: 4px;
        background: #fff;
        padding: 2px;
    }
</style>
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Rice Bag Products
            <small>Review &amp; verify</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li>Vendor Products</li>
            <li><a href="{{ route('get.web.rice.bag.products.list') }}">Rice Bag Products</a></li>
            <li class="active">List</li>
        </ol>
    </section>

    <section class="content">
        <div class="box-body">
            <div class="row text-left" style="margin-top: 20px;">
                <div class="col-md-12">
                    <table class="table table-striped table-bordered" id="riceBagProductsTable" width="100%">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Owner</th>
                            <th>Bag type</th>
                            <th>Packing form</th>
                            <th>Specification</th>
                            <th>Packing sizes</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                                @php
                                    $firstImage = optional($product->packingSizes->first())->image;
                                    $imageUrl = $firstImage
                                        ? asset('uploads/rice-bag-products/'.$product->user_id.'/'.$firstImage)
                                        : null;
                                @endphp
                                <tr>
                                    <td>{{ $product->id }}</td>
                                    <td>
                                        {{ optional($product->user)->name ?? '—' }}
                                        @if(optional($product->user)->email)
                                            <br><small>{{ $product->user->email }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $bagTypes[$product->bag_type_id] ?? ($product->bag_type_id ? '#'.$product->bag_type_id : '—') }}
                                        @if(!empty($product->other_type_value))
                                            <br><small class="text-muted">{{ $product->other_type_value }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $product->packing_form ?? '—' }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($product->specification ?? '—', 60) }}</td>
                                    <td style="white-space:nowrap;">
                                        @if($imageUrl)
                                            <img src="{{ $imageUrl }}" alt="" class="rice-bag-thumb" onerror="this.style.display='none';">
                                        @endif
                                        {{ $product->packingSizes->count() }} size(s)
                                    </td>
                                    <td>
                                        @if((int) $product->status === 1)
                                            <span class="label label-success">Verified / Active</span>
                                        @else
                                            <span class="label label-warning">Pending review</span>
                                        @endif
                                    </td>
                                    <td>{{ $product->created_at ? \Carbon\Carbon::parse($product->created_at)->format('d-m-Y H:i') : '—' }}</td>
                                    <td style="white-space:nowrap;">
                                        <a class="btn btn-sm btn-primary" href="{{ route('get.web.rice.bag.products.show', $product->id) }}">
                                            <i class="fa fa-eye"></i> View
                                        </a>
                                        @include('components.vendor-product-status-actions', [
                                            'product' => $product,
                                            'route' => 'toggle.web.rice.bag.products.status',
                                        ])
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@section('javascript')
@include('components.vendor-product-deactivate-modal')
<script>
    $(function () {
        $('#riceBagProductsTable').DataTable({
            pageLength: 25,
            order: [[0, 'desc']],
            columnDefs: [{ orderable: false, targets: [8] }]
        });
    });
</script>
@endsection
