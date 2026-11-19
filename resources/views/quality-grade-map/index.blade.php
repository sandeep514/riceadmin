@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                Quality Grade Map
                <small>List</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="javascript:void(0)"><i class="fa fa-dashboard"></i> Home</a></li>
                <li class="active">Quality Grade Map</li>
            </ol>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-xs-12">
                    <div class="box">
                        <div class="box-header">
                            <h3 class="box-title">Map quality + form to wand grades</h3>
                            <div class="pull-right">
                                <a href="{{ route('create.quality-grade-map') }}" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> Create New</a>
                            </div>
                        </div>
                        <div class="box-body">
                            <p class="help-block" style="margin-top:0;">
                                Example: <strong>1121 + Steam</strong> → <em>4 Wands</em>, <em>2 Short Wands</em>, etc.
                            </p>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped datatable" width="100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Category</th>
                                            <th>Quality</th>
                                            <th>Form</th>
                                            <th>Grades (Wands)</th>
                                            <th>Updated</th>
                                            <th width="150">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($records as $record)
                                            <tr>
                                                <td>{{ $record->id }}</td>
                                                <td><span class="label label-{{ $record->quality_type == 'basmati' ? 'info' : 'warning' }}">{{ ucfirst($record->quality_type) }}</span></td>
                                                <td>{{ $record->riceName ? $record->riceName->name : '-' }}</td>
                                                <td>{{ $record->form ? $record->form->name : '-' }}</td>
                                                <td>{{ $record->grade_names }}</td>
                                                <td>{{ $record->updated_at ? $record->updated_at->format('d M Y H:i') : '-' }}</td>
                                                <td>
                                                    @include('quality-grade-map._actions', ['model' => $record])
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
