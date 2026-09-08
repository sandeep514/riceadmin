@extends('layouts.main')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Site Policies
            <small>Terms, privacy, disclaimer &amp; more</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="javascript:void(0)"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Site Policies</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">Policy master</h3>
                        <div class="pull-right">
                            <a href="{{ route('create.site.policy') }}" class="btn btn-primary btn-sm">
                                <i class="fa fa-plus"></i> Add Policy
                            </a>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped datatable" width="100%">
                                <thead>
                                    <tr>
                                        <th width="100">Order</th>
                                        <th>Title</th>
                                        <th>Slug</th>
                                        <th>PDF</th>
                                        <th>Status</th>
                                        <th>Updated</th>
                                        <th width="220">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($records as $record)
                                        <tr>
                                            <td data-order="{{ $record->order_no ?? 999999 }}">
                                                @include('components.master-order-input', ['model' => $record, 'route' => 'update.order.site.policy'])
                                            </td>
                                            <td>{{ $record->title }}</td>
                                            <td><code>{{ $record->slug }}</code></td>
                                            <td>
                                                @if($record->pdf_path)
                                                    <a href="{{ asset($record->pdf_path) }}" target="_blank" rel="noopener">
                                                        <i class="fa fa-file-pdf-o"></i> View PDF
                                                    </a>
                                                @else
                                                    <span class="text-muted">Not generated</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if((int) $record->status === 1)
                                                    <span class="label label-success">Active</span>
                                                @else
                                                    <span class="label label-default">Inactive</span>
                                                @endif
                                            </td>
                                            <td>{{ $record->updated_at ? $record->updated_at->format('d-m-Y H:i') : '—' }}</td>
                                            <td style="white-space: nowrap;">
                                                <a href="{{ route('edit.site.policy', $record->id) }}" class="btn btn-primary btn-xs">
                                                    <i class="fa fa-edit"></i> Edit
                                                </a>
                                                <a href="{{ route('regenerate.site.policy.pdf', $record->id) }}" class="btn btn-info btn-xs"
                                                   onclick="return confirm('Regenerate PDF for this policy?');">
                                                    <i class="fa fa-refresh"></i> PDF
                                                </a>
                                                <a href="{{ route('site.policy.change-status', $record->id) }}" class="btn btn-warning btn-xs">
                                                    {{ (int) $record->status === 1 ? 'Inactive' : 'Active' }}
                                                </a>
                                                <form action="{{ route('delete.site.policy', $record->id) }}" method="POST" style="display:inline;"
                                                      onsubmit="return confirm('Delete this policy?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-xs">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">No policies yet. Add Terms and Conditions first.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <p class="help-block" style="margin-top:12px;">
                            Use slug <code>terms_and_conditions</code> for the PDF attached to welcome registration emails.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
