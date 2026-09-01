@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                Vendor Packing Type
                <small>List</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="javascript:void(0)"><i class="fa fa-dashboard"></i> Home</a></li>
                <li>Vendor Flow</li>
                <li class="active">Vendor Packing Type</li>
            </ol>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-xs-12">
                    <div class="box">
                        <div class="box-header">
                            <h3 class="box-title">Vendor packing type master</h3>
                            <div class="pull-right">
                                <a href="{{ route('create.vendor-packing-type') }}" class="btn btn-primary btn-sm">
                                    <i class="fa fa-plus"></i> Create New
                                </a>
                            </div>
                        </div>
                        <div class="box-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped datatable" width="100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Description</th>
                                            <th>Status</th>
                                            <th>Created</th>
                                            <th>Updated</th>
                                            <th width="180">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($records as $record)
                                            <tr>
                                                <td>{{ $record->id }}</td>
                                                <td>{{ $record->name }}</td>
                                                <td>{{ $record->description ?: '—' }}</td>
                                                <td>
                                                    @if((int) $record->status === \App\VendorPackingType::STATUS_ACTIVE)
                                                        <span class="label label-success">Active</span>
                                                    @else
                                                        <span class="label label-default">Inactive</span>
                                                    @endif
                                                </td>
                                                <td>{{ $record->created_at ? $record->created_at->format('d M Y H:i') : '—' }}</td>
                                                <td>{{ $record->updated_at ? $record->updated_at->format('d M Y H:i') : '—' }}</td>
                                                <td>
                                                    @include('vendor-packing-types._actions', ['model' => $record])
                                                </td>
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
            columnDefs: [{ orderable: false, targets: [6] }]
        });

        $(document).on('click', '.delete-row', function(e){
            e.preventDefault();
            if(confirm('Are you sure you want to delete this vendor packing type?')){
                $(this).closest('form').submit();
            }
        });
    });
</script>
@endsection
