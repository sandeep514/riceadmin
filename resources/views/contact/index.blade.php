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
                        <form method="POST" action="{{ route('save.usd.coupon') }}">
                            @csrf
                            <div class="row" style="padding: 0 10px;">
                                <div class="col-md-6">
                                    {!! Form::label('plan_name','Coupon Name') !!}
                                    {!! Form::text('couponName', '' ,['class'=>'form-control capitalize', 'required' => 'required']) !!}
                                </div>
                                <div class="col-md-6">
                                    {!! Form::label('plan_name','Coupon Feature') !!}
                                    {!! Form::text('couponFeature', '' ,['class'=>'form-control', 'required' => 'required']) !!}
                                </div>
                                <div class="col-md-6">
                                    {!! Form::label('plan_name','Description') !!}
                                    {!! Form::text('description', '' ,['class'=>'form-control', 'required' => 'required']) !!}
                                </div>
                                <div class="col-md-6">
                                    {!! Form::label('plan_name','Coupon %') !!}
                                    {!! Form::number('discountPercentage', '' ,['class'=>'form-control', 'required' => 'required']) !!}
                                </div>
                                <div class="col-md-6">
                                    {!! Form::label('plan_name','Max Discount') !!}
                                    {!! Form::number('maxDiscount', '' ,['class'=>'form-control', 'required' => 'required']) !!}
                                </div>
                                <div class="col-md-6">
                                    {!! Form::label('plan_name','Coupon Expiry Date') !!}
                                    {!! Form::date('expiryDate', '' ,['class'=>'form-control', 'required' => 'required']) !!}
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
                                    <th>Coupon Name</th>
                                    <th>Coupon Feature </th>
                                    <th>Coupon Description</th>
                                    <th>Coupon Percentage</th>
                                    <th>Coupon Expiry</th>
                                    <th>Max Discount</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($coupon as $k => $v)
                                    <tr>
                                        <td style="text-transform : uppercase;">{{ $v->coupon_name }}</td>
                                        <td style="text-align: center">{{ $v->coupon_feature }}</td>
                                        <td style="text-align: center">{{ $v->coupon_description  }}</td>
                                        <td style="text-align: center">{{ $v->coupon_percentage  }}%</td>
                                        <td style="text-align: center">{{ $v->coupon_expiry  }}</td>
                                        <td style="text-align: center">{{ $v->maxDiscount  }}</td>
                                        <td style="text-align: center"><a href="{{ route('change.status.usd.coupon' , $v->id) }}" class="btn {{ ($v->status == 1)? 'btn-danger' : 'btn-info' }} btn-xs" />{{ ($v->status == 1)? 'De-Activate' : 'Activate' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            
                            <tfoot>
                                <tr>
                                    <th>Coupon Name</th>
                                    <th>Coupon Feature </th>
                                    <th>Coupon Description</th>
                                    <th>Coupon Percentage</th>
                                    <th>Coupon Expiry</th>
                                    <th>Max Discount</th>
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