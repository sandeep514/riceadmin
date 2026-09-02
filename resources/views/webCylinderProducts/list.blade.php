@extends('layouts.main')

@section('content')
<style>
    .vendor-product-thumb {
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
            Cylinder Products
            <small>Review &amp; verify</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li>Vendor Products</li>
            <li><a href="{{ route('get.web.cylinder.products.list') }}">Cylinder Products</a></li>
            <li class="active">List</li>
        </ol>
    </section>

    <section class="content">
        <div class="box-body">
            <div class="row text-left" style="margin-top: 20px;">
                <div class="col-md-12">
                    <table class="table table-striped table-bordered" id="cylinderProductsTable" width="100%">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Owner</th>
                            <th>Cylinder type</th>
                            <th>Specification</th>
                            <th>Variants</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                            @foreach($products as $product)
                                @php
                                    $firstImage = optional($product->variants->first())->image;
                                    $imageUrl = $firstImage
                                        ? asset('uploads/cylinder-products/'.$product->user_id.'/'.$firstImage)
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
                                    <td>{{ $types[$product->cylinder_type_id] ?? ($product->cylinder_type_id ? '#'.$product->cylinder_type_id : '—') }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($product->specification ?? '—', 60) }}</td>
                                    <td style="white-space:nowrap;">
                                        @if($imageUrl)
                                            <img src="{{ $imageUrl }}" alt="" class="vendor-product-thumb" onerror="this.style.display='none';">
                                        @endif
                                        {{ $product->variants->count() }} variant(s)
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
                                        <a class="btn btn-sm btn-primary" href="{{ route('get.web.cylinder.products.show', $product->id) }}">
                                            <i class="fa fa-eye"></i> View
                                        </a>
                                        @include('components.vendor-product-status-actions', [
                                            'product' => $product,
                                            'route' => 'toggle.web.cylinder.products.status',
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
        $('#cylinderProductsTable').DataTable({
            pageLength: 25,
            order: [[0, 'desc']],
            columnDefs: [{ orderable: false, targets: [7] }]
        });
    });
</script>
@endsection
