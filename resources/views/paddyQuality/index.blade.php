@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                Paddy Quality
                <small>Master</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li class="active">Paddy Quality</li>
            </ol>
        </section>

        <section class="content">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12 text-left" style="margin-top: 20px;">
                        <a href="{{ route('create.paddy.quality') }}" class="btn btn-sm btn-info">Create</a>
                        <div class="col-md-12 inputs" style="padding-left: 0; margin-top: 15px;">
                            <table class="table table-striped table-bordered paddy-quality-datatable" width="100%">
                                <thead>
                                <tr>
                                    <th>Order</th>
                                    <th>Quality</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                    @foreach($paddyQualities as $value)
                                        <tr>
                                            <td>
                                                <form method="POST" action="{{ route('update.order.paddy.quality') }}" class="form-inline">
                                                    @csrf
                                                    <input type="hidden" name="id" value="{{ $value->id }}">
                                                    <div class="input-group input-group-sm" style="width:130px;">
                                                        <input type="number" name="order" class="form-control" min="1" value="{{ $value->order }}" required>
                                                        <span class="input-group-btn">
                                                            <button type="submit" class="btn btn-info" title="Change order">
                                                                <i class="fa fa-save"></i>
                                                            </button>
                                                        </span>
                                                    </div>
                                                </form>
                                            </td>
                                            <td style="text-transform: capitalize;">{{ $value->quality }}</td>
                                            <td>{{ $value->description }}</td>
                                            <td>{{ ((int) $value->status === 1) ? 'Active' : 'Inactive' }}</td>
                                            <td>
                                                <a class="btn btn-sm btn-info" href="{{ route('edit.paddy.quality', $value->id) }}">Edit</a>
                                                <a class="btn btn-sm btn-{{ ((int) $value->status === 1) ? 'danger' : 'success' }}"
                                                   href="{{ route('update.status.paddy.quality', $value->id) }}">
                                                    {{ ((int) $value->status === 1) ? 'De-active' : 'Active' }}
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
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
<script>
    $(function () {
        $('.paddy-quality-datatable').DataTable({
            pageLength: 25,
            order: [[0, 'asc']],
            columnDefs: [{ orderable: false, targets: [4] }]
        });
    });
</script>
@endsection
