@extends('layouts.main')

@section('content')
    <style type="text/css">
        .capitalize{
            text-transform: uppercase;
        }
    </style>
    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                USD Coupon
                <small>Create</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="{{ route('designations') }}">USD Coupon</a></li>
                <li class="active">Create</li>
            </ol>
        </section>
        <section class="content">
            <div class="row">
                <!-- left column -->
                <div class="col-md-12">
                    <!-- general form elements -->
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">USD Coupon Details</h3>
                        </div>
                        <!-- /.box-header -->
                        <form method="POST" action="{{ route('save.usd.plan') }}">
                            @csrf
                            <div class="row" style="padding: 0 10px;">
                                <div class="col-md-6">
                                    {!! Form::label('plan_name','Plan Name') !!}
                                    {!! Form::text('planname', '' ,['class'=>'form-control', 'required' => 'required']) !!}
                                </div>
                                <div class="col-md-6">
                                    {!! Form::label('plan_name','Plan Description') !!}
                                    {!! Form::text('plandesc', '' ,['class'=>'form-control', 'required' => 'required']) !!}
                                </div>
                                <div class="col-md-6">
                                    {!! Form::label('plan_name','Valid Month') !!}
                                    {!! Form::number('validmonth', '' ,['class'=>'form-control', 'required' => 'required']) !!}
                                </div>
                                <div class="col-md-6">
                                    {!! Form::label('plan_name','Actual Price') !!}
                                    {!! Form::number('actual_price', '' ,['class'=>'form-control', 'required' => 'required']) !!}
                                </div>
                                <div class="col-md-6">
                                    {!! Form::label('plan_name','Discounted Price') !!}
                                    {!! Form::number('discounted_prie', '' ,['class'=>'form-control', 'required' => 'required']) !!}
                                </div>
                            </div>

                            <div class="box-footer">
                                <button type="submit" class="btn btn-primary">Save</button>
                            </div>
                        </form>
                    </div>
                    <!-- /.box -->
                    <!-- usdPrice -->

                    <div class="col-md-12">
                        <table id="example2" class="display" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th>Plan Name</th>
                                    <th>Plan Desc</th>
                                    <th>Valid Months</th>
                                    <th>Actual Price</th>
                                    <th>Discounted Price</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($usdPlan as $k => $v)
                                    <tr>
                                        <td style="text-transform : uppercase;">{{ $v->plan_name }}</td>
                                        <td style="text-align: center">{{ $v->plan_desc }}</td>
                                        <td style="text-align: center">{{ $v->valid_months  }}</td>
                                        <td style="text-align: center">{{ $v->actual_price  }}</td>
                                        <td style="text-align: center">{{ $v->discounted_prie  }}</td>
                                        <td style="text-align: center"><a href="{{ route('change.status.usd.plan' , $v->id) }}" class="btn {{ ($v->status == 1)? 'btn-danger' : 'btn-info' }} btn-xs" />{{ ($v->status == 1)? 'De-Activate' : 'Activate' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            
                            <tfoot>
                                <tr>
                                    <th>Plan Name</th>
                                    <th>Plan Desc</th>
                                    <th>Valid Months</th>
                                    <th>Actual Price</th>
                                    <th>Discounted Price</th>
                                    <th>Actions</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>

@endsection
@section('scripts')
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript">
        // $(document).ready(function() {
        //     $('#example2').DataTable( {
        //       pageLength : 50,
        //     } );
        // } );
    </script>
@endsection