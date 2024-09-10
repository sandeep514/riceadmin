@extends('layouts.main')
@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                Create New Price
                <small>Create</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="#">Live Prices</a></li>
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
                            <h3 class="box-title">Price Details</h3>
                        </div>
                        <!-- /.box-header -->
                        {!! Form::open(['route'=>'save.price' , 'id' => 'formCreate']) !!}
                            @include('live_prices._form')
                            <div class="box-footer">
                                <button type="submit" class="btn btn-primary">Save Price</button>
                            </div>
                        {!! Form::close() !!}
                    </div>
                    <a href="javascript:void(0)" class="submitButton" >Submit</a>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">Price List</h3>
                        </div>
                        <div class="box-body">
                            @php
                                if(request()->has('date')){
                                    $date = request()->date;
                                }else{
                                    $date = \Carbon\Carbon::now()->format('d-m-Y');
                                }
                            @endphp
                            {!! Form::open(['method'=>'get']) !!}
                                <div class="row">
                                    <div class="col-md-6">
                                        {!! Form::label('from','From') !!}
                                        {!! Form::text('from',$date,['class'=> 'datepicker','autocomplete'=>'off']) !!}

                                        {!! Form::label('to','To') !!}
                                        {!! Form::text('to',$date,['class'=> 'datepicker','autocomplete'=>'off']) !!}
                                        
                                        {!! Form::submit('Show Prices',['class'=> 'btn btn-primary btn-sm','style'=>'margin-top: -4px']) !!}
                                    </div>
                                </div>
                            {!! Form::close() !!}
                            <div class="divider"></div>
                            <div class="row">
                                <div class="col-md-12">
                                    <table class="table table-striped table-bordered datatable" id="example2"> 
                                        <thead>
                                            <tr>
                                                <th>Sr. No.</th>
                                                <th>Name</th>
                                                <th>Form</th>
                                                <th>Min Price</th>
                                                <th>Max Price</th>
                                                <th>State</th>
                                                <th>Up/Down</th>
                                                <th>Created At</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($prices as $key => $price)
                                                @if( $price->name_rel != null && $price->form_rel )
                                                    <tr>
                                                        <td>{{ $loop->index + 1 }}</td>
                                                        <td>{{ $price->name_rel->name }}</td>
                                                        <td>{{ $price->form_rel->form_name }} ({{ ucfirst($price->form_rel->type) }})</td>
                                                        <td>{{ $price->min_price }}</td>
                                                        <td>{{ $price->max_price }}</td>
                                                        <td>{{ ucwords(str_replace('_',' ',$price->state)) }}</td>
                                                        <td>{{ $price->up_down }}</td>
                                                        <td>{{ $price->created_at->diffForHumans() }}</td>
                                                        <td>
                                                            <a href="{{ route('delete.price',$price->id) }}" onclick="return confirm('Are you sure to delete this price?')" class="btn btn-danger btn-xs">Delete</a>
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
@section('javascript')
    <script type="text/javascript">
        $(document).ready(function(){
            $('.submitButton').click(function(){
               var formData = new FormData(document.getElementById("formCreate"));
               
               // for (var [key, value] of formData.entries()) { 
               //    console.log(key, value);
               //  }
                var object = {};
                formData.forEach((value, key) => object[key] = value);
                var json = JSON.stringify(object);


                let formData2 = new FormData();
                formData2.append('_token' , $('meta[name="csrf-token"]').attr('content') );
                formData2.append('data' ,json );

                console.log(formData2);
                const request = new XMLHttpRequest();
                request.open("POST", "{{route('save.price')}}");
                request.send((formData2));
                // console.log(formData);

            })
        })
    </script>
@endsection
