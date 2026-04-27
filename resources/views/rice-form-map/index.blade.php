@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                Rice Form Mapping
                <small>List</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="javascript:void(0)"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="javascript:void(0)"><i class="fa fa-dashboard"></i> Rice Form Map</a></li>
                <li class="active">List</li>
            </ol>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-xs-12">
                    <div class="box">
                        <div class="box-header">
                            <h3 class="box-title">List of Rice Form Mappings</h3>
                            <div class="pull-right">
                                <a href="{{ route('create.rice-form-map') }}" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Create New</a>
                            </div>
                        </div>
                        <div class="box-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped datatable" width="100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Rice Name</th>
                                            <th>Group Name</th>
                                            <th>Forms</th>
                                            <th>Created At</th>
                                            <th width="150">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($records as $record)
                                            <tr>
                                                <td>{{ $record->id }}</td>
                                                <td>{{ $record->riceName ? $record->riceName->name : '-' }}</td>
                                                <td>{{ $record->group_name }}</td>
                                                <td>{{ $record->form_names }}</td>
                                                <td>{{ $record->created_at->format('d M Y H:i') }}</td>
                                                <td>
                                                    @include('rice-form-map._actions', ['model' => $record])
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
        });

        $(document).on('click', '.delete-row', function(e){
            e.preventDefault();
            if(confirm('Are you sure you want to delete this record?')){
                $(this).closest('form').submit();
            }
        });
    });
</script>
@endsection
