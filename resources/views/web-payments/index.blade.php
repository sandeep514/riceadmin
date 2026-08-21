@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                Payments
                <small>List</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="javascript:void(0)"><i class="fa fa-dashboard"></i> Home</a></li>
                <li class="active">Payments</li>
            </ol>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-xs-12">
                    <div class="box">
                        <div class="box-header">
                            <h3 class="box-title">All payments</h3>
                        </div>
                        <div class="box-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped datatable" width="100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Customer name</th>
                                            <th>Customer email</th>
                                            <th>Mobile</th>
                                            <th>Role</th>
                                            <th>Category</th>
                                            <th>Plan</th>
                                            <th>Amount</th>
                                            <th>Transaction ID</th>
                                            <th>Invoice</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($payments as $p)
                                            @php
                                                $user = $p->userRel;
                                                $plan = $p->planRel;
                                                $roleName = $plan && $plan->roleRel
                                                    ? $plan->roleRel->role_name
                                                    : ($user && isset($roleNames[$user->role]) ? $roleNames[$user->role] : '—');
                                                $categoryName = $plan && $plan->categoryRel
                                                    ? $plan->categoryRel->category
                                                    : '—';
                                                $amount = $p->displayAmount();
                                                $currency = $p->currency ?: 'INR';
                                                $hasInvoice = $p->invoiceAbsolutePath() !== null;
                                            @endphp
                                            <tr>
                                                <td>{{ $p->id }}</td>
                                                <td>{{ $user->name ?? '—' }}</td>
                                                <td>{{ $user->email ?? '—' }}</td>
                                                <td>{{ $user->mobile ?? ($user->phone ?? '—') }}</td>
                                                <td>{{ $roleName ?: '—' }}</td>
                                                <td>{{ $categoryName ?: '—' }}</td>
                                                <td>{{ $plan->title ?? '—' }}@if($p->subscription_type) <small>({{ $p->subscription_type }})</small>@endif</td>
                                                <td>
                                                    @if($amount !== null)
                                                        {{ $currency }} {{ number_format((float) $amount, 2) }}
                                                    @else
                                                        —
                                                    @endif
                                                </td>
                                                <td>{{ $p->payment_id ?: '—' }}</td>
                                                <td>
                                                    <a href="{{ route('web-payments.invoice', $p->id) }}" class="btn btn-xs btn-primary" target="_blank">
                                                        <i class="fa fa-file-pdf-o"></i> {{ $hasInvoice ? 'Download' : 'Generate' }}
                                                    </a>
                                                </td>
                                                <td>{{ $p->created_at ? $p->created_at->format('d M Y H:i') : '—' }}</td>
                                            </tr>
                                        @endforeach
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

@section('javascript')
<script>
    $(function(){
        $('.datatable').DataTable({
            pageLength: 25,
            order: [[0, 'desc']],
            columnDefs: [{ orderable: false, targets: [9] }]
        });
    });
</script>
@endsection
