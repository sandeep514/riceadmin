@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                Post a job
                <small>List</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="javascript:void(0)"><i class="fa fa-dashboard"></i> Home</a></li>
                <li class="active">Post a job</li>
            </ol>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-xs-12">
                    <div class="box">
                        <div class="box-header">
                            <h3 class="box-title">Posted jobs</h3>
                            <div class="box-tools pull-right">
                                <a href="{{ route('create.post-a-job') }}" class="btn btn-primary btn-sm">
                                    <i class="fa fa-plus"></i> Add job
                                </a>
                            </div>
                        </div>
                        <table id="example2" class="display table table-bordered table-striped" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th style="text-align: center">Title</th>
                                    <th style="text-align: center">Role</th>
                                    <th style="text-align: center">Location</th>
                                    <th style="text-align: center">Type</th>
                                    <th style="text-align: center">Last date to apply</th>
                                    <th style="text-align: center">Positions</th>
                                    <th style="text-align: center">Status</th>
                                    <th style="text-align: center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($jobs as $v)
                                    <tr>
                                        <td>{{ $v->title }}</td>
                                        <td style="text-align: center">{{ $v->job_role ?: '—' }}</td>
                                        <td style="text-align: center">{{ $v->location ?: '—' }}</td>
                                        <td style="text-align: center">{{ \App\PostedJob::employmentTypeOptions()[$v->employment_type] ?? ($v->employment_type ?: '—') }}</td>
                                        <td style="text-align: center">{{ $v->last_date_apply ? $v->last_date_apply->format('d-m-Y') : '—' }}</td>
                                        <td style="text-align: center">{{ $v->number_of_positions }}</td>
                                        <td style="text-align: center">
                                            @if((int) $v->status === \App\PostedJob::STATUS_ACTIVE)
                                                <span class="label label-success">Active</span>
                                            @else
                                                <span class="label label-default">Deactive</span>
                                            @endif
                                        </td>
                                        <td style="text-align: center; white-space: nowrap;">
                                            @if((int) $v->status === \App\PostedJob::STATUS_ACTIVE)
                                                <a href="{{ route('post-a-job.change-status', ['id' => $v->id, 'status' => \App\PostedJob::STATUS_INACTIVE]) }}" class="btn btn-warning btn-xs" onclick="return confirm('Mark this job as deactive? It will be hidden from the public job list.');">Deactive</a>
                                            @else
                                                <a href="{{ route('post-a-job.change-status', ['id' => $v->id, 'status' => \App\PostedJob::STATUS_ACTIVE]) }}" class="btn btn-success btn-xs">Active</a>
                                            @endif
                                            <a href="{{ route('edit.post-a-job', $v->id) }}" class="btn btn-info btn-xs">Edit</a>
                                            {!! Form::open(['route' => ['delete.post-a-job', $v->id], 'method' => 'delete', 'style' => 'display:inline', 'onsubmit' => "return confirm('Delete this job posting?');"]) !!}
                                                <button type="submit" class="btn btn-danger btn-xs">Delete</button>
                                            {!! Form::close() !!}
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
