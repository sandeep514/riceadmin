@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <h1>
                Qualities
                <small>List</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="javascript:void(0)"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="javascript:void(0)"><i class="fa fa-dashboard"></i> Qualities</a></li>
                <li class="active">Dashboard</li>
            </ol>
        </section>
        <section class="content">
            <div class="row">
                <div class="col-xs-12">
                    <div class="box">
                        <div class="box-header">
                            <h3 class="box-title">Quality of roles</h3>
                        </div>
                        <!-- /.box-header -->
                        <div class="box-body">
                            <div class="table-responsive">
                                <div class="col-md-12">
                                    <table id="example2" class="display" style="width: 100%;">
                                        <thead>
                                            <tr>
                                                <th style="text-align: center ">Title</th>
                                                <th style="text-align: center ">Type</th>
                                                <th style="text-align: center ">Order</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($data as $k => $v)
                                                <tr>
                                                    <td>{{ $v->form_name }}</td>
                                                    <td>{{ $v->type }}</td>
                                                    <td>
                                                        <form method="post" action="{{ route('update.rice.form.order') }}">
                                                            @csrf
                                                            <input type="text" name="order" value="{{ $v->order  }}">
                                                            <input type="hidden" name="nameId" value="{{ $v->id  }}">
                                                            <input type="submit" name="submit" value="Change Order" class="btn btn-info btn-sm">
                                                        </form>
                                                    </td>
                                                </tr>
                                                
                                            @endforeach
                                        </tbody>

                                        <tfoot>
                                            <tr>
                                                <th style="text-align: center ">Title</th>
                                                <th style="text-align: center ">Type</th>
                                                <th style="text-align: center ">Order</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <!-- /.box-body -->
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@section('scripts')
@endsection
