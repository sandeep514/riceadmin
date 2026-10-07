@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                Job Applications
                <small>List</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="javascript:void(0)"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="{{ route('post-a-job') }}">Post a job</a></li>
                <li class="active">Applications</li>
            </ol>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-xs-12">
                    <div class="box">
                        <div class="box-header">
                            <h3 class="box-title">Job applications ({{ $applications->count() }})</h3>
                        </div>
                        <div class="box-body">
                            <form method="GET" action="{{ route('post-a-job.applications') }}" class="form-inline" style="margin-bottom: 15px;">
                                <div class="form-group" style="margin-right: 10px;">
                                    <label for="job-filter" style="margin-right: 5px;">Vacancy:</label>
                                    <select name="job_id" id="job-filter" class="form-control input-sm" onchange="if(this.value){window.location='{{ url('administrator/master/post-a-job/applications') }}/'+this.value;}else{window.location='{{ route('post-a-job.applications') }}';}">
                                        <option value="">-- All Vacancies --</option>
                                        @foreach($jobs as $job)
                                            <option value="{{ $job->id }}" {{ (string)($jobId ?? '') === (string)$job->id ? 'selected' : '' }}>{{ $job->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <a href="{{ route('post-a-job.applications') }}" class="btn btn-default btn-sm">Reset</a>
                            </form>
                            <div class="table-responsive">
                                <table id="example2" class="display table table-bordered table-striped" style="width: 100%;">
                                    <thead>
                                        <tr>
                                            <th style="text-align: center">Vacancy</th>
                                            <th style="text-align: center">Name</th>
                                            <th style="text-align: center">Email</th>
                                            <th style="text-align: center">Mobile</th>
                                            <th style="text-align: center">Experience</th>
                                            <th style="text-align: center">CV</th>
                                            <th style="text-align: center">Applied At</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($applications as $app)
                                            <tr>
                                                <td style="text-align: center">{{ optional($app->postedJob)->title ?: '—' }}</td>
                                                <td style="text-align: center">{{ $app->name }}</td>
                                                <td style="text-align: center">{{ $app->email }}</td>
                                                <td style="text-align: center">{{ $app->mobile }}</td>
                                                <td style="text-align: center">{{ $app->experience ? \Illuminate\Support\Str::limit($app->experience, 80) : '—' }}</td>
                                                <td style="text-align: center">
                                                    @if($app->cv_file)
                                                        <a href="{{ asset($app->cv_file) }}" target="_blank" class="btn btn-info btn-xs">View CV</a>
                                                    @else
                                                        —
                                                    @endif
                                                </td>
                                                <td style="text-align: center">{{ $app->created_at ? $app->created_at->format('d-m-Y H:i') : '—' }}</td>
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
