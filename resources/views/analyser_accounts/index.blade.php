@extends('layouts.main')
@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                Analyser Accounts
                <small>List</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="javascript:void(0)"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="javascript:void(0)">User Management</a></li>
                <li class="active">Analyser</li>
            </ol>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-xs-12">
                    <div class="box">
                        <div class="box-header">
                            <h3 class="box-title">List of analyser accounts</h3>
                            <a href="{{ route('analyser-accounts.create') }}" class="btn btn-primary btn-sm pull-right">Create New</a>
                        </div>
                        <div class="box-body">
                            <div class="table-responsive">
                                <table id="example2" class="display" style="width: 100%;">
                                    <thead>
                                        <tr>
                                            <th style="text-align: center">Name</th>
                                            <th style="text-align: center">Email</th>
                                            <th style="text-align: center">Contact Person</th>
                                            <th style="text-align: center">Mobile</th>
                                            <th style="text-align: center">Start Date</th>
                                            <th style="text-align: center">End Date</th>
                                            <th style="text-align: center">Historical</th>
                                            <th style="text-align: center">Today</th>
                                            <th style="text-align: center">Downloads</th>
                                            <th style="text-align: center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($accounts as $account)
                                            <tr>
                                                <td style="text-align: center">{{ optional($account->user)->name }}</td>
                                                <td style="text-align: center">{{ optional($account->user)->email }}</td>
                                                <td style="text-align: center">{{ $account->contact_person_name }}</td>
                                                <td style="text-align: center">{{ $account->contact_mobile }}</td>
                                                <td style="text-align: center">{{ $account->start_date ? $account->start_date->format('d-m-Y') : '-' }}</td>
                                                <td style="text-align: center">{{ $account->end_date ? $account->end_date->format('d-m-Y') : '-' }}</td>
                                                <td style="text-align: center">{{ $account->has_historical_access ? 'Yes' : 'No' }}</td>
                                                <td style="text-align: center">{{ $account->has_today_access ? 'Yes' : 'No' }}</td>
                                                <td style="text-align: center">{{ (int) $account->downloads_used }} / {{ $account->download_limit ?? 'Unlimited' }}</td>
                                                <td style="text-align: center">
                                                    <a href="{{ route('analyser-accounts.edit', $account->id) }}" class="btn btn-info btn-xs">Edit</a>
                                                    {!! Form::open(['method'=>'DELETE','route'=>['analyser-accounts.destroy', $account->id],'class'=>'delete-form','style'=>'display: inline-block;']) !!}
                                                    <a href="javascript:void(0)" class="btn btn-danger btn-xs delete-row">Delete</a>
                                                    {!! Form::close() !!}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th style="text-align: center">Name</th>
                                            <th style="text-align: center">Email</th>
                                            <th style="text-align: center">Contact Person</th>
                                            <th style="text-align: center">Mobile</th>
                                            <th style="text-align: center">Start Date</th>
                                            <th style="text-align: center">End Date</th>
                                            <th style="text-align: center">Historical</th>
                                            <th style="text-align: center">Today</th>
                                            <th style="text-align: center">Downloads</th>
                                            <th style="text-align: center">Action</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
