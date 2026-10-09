@extends('layouts.main')

@section('content')

    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                Calculator
                <small>Create</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="{{ route('designations') }}">Calculator</a></li>
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
                            <h3 class="box-title">Calculator Details</h3>
                        </div>
                        <!-- /.box-header -->
                        <form method="POST" action="{{ route('calculator.save') }}">
                            @csrf
                            @include('calculator._form')
                            <div class="box-footer">
                                <button type="button" class="btn btn-info calculate">Calculate</button>
                                <button type="submit" class="btn btn-primary" style="float: right;">POST</button>
                            </div>                            
                        </form>
                    </div>

                    <div class="col-md-12">
                        @if(!empty($showingFallback))
                            <div class="alert alert-warning" style="margin-bottom: 15px;">
                                Today's prices ({{ $today }}) not added yet — showing last available date {{ $maxDate }}.
                                Use Clone on a row to carry it forward to today, or POST a new calculation above.
                            </div>
                        @endif
                        <form method="GET" action="{{ route('create.calculator') }}" class="form-inline" style="margin-bottom: 15px;">
                            <div class="form-group" style="margin-right: 10px;">
                                <label for="from" style="margin-right: 5px;">From:</label>
                                <input type="date" name="from" id="from" class="form-control input-sm" value="{{ $from ?? '' }}">
                            </div>
                            <div class="form-group" style="margin-right: 10px;">
                                <label for="to" style="margin-right: 5px;">To:</label>
                                <input type="date" name="to" id="to" class="form-control input-sm" value="{{ $to ?? '' }}">
                            </div>
                            <div class="form-group" style="margin-right: 10px;">
                                <label for="packing" style="margin-right: 5px;">Packing:</label>
                                <select name="packing" id="packing" class="form-control input-sm">
                                    <option value="">-- All Packing --</option>
                                    @foreach($packings ?? [] as $pack)
                                        <option value="{{ $pack->id }}" {{ (string)($packing ?? '') === (string)$pack->id ? 'selected' : '' }}>{{ $pack->bag_size }} - {{ $pack->bag_type }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-info btn-sm">Filter</button>
                            <a href="{{ route('create.calculator') }}" class="btn btn-default btn-sm">Reset</a>
                            <a href="{{ route('export.calculator', array_filter(['from' => $from ?? null, 'to' => $to ?? null, 'packing' => $packing ?? null])) }}" class="btn btn-success btn-sm">Export to Excel</a>
                            <span class="text-muted" style="margin-left: 10px;">Total: {{ $usdPrice->total() }}</span>
                        </form>
                        <table id="example2" class="display" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th>Quality</th>
                                    <th>Rice min & max </th>
                                    <th>Packing</th>
                                    <th>Packing Name</th>
                                    <th>Rice Type</th>
                                    <th>Transport</th>
                                    <th>USD Rate</th>
                                    <th>Fob</th>
                                    <th>created_at</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($usdPrice as $k => $v)
                                    @if($v->getRiceQuality != null && $v->getUSDDefaultMaster != null)
                                        <tr>
                                            <td> 
                                                {{$v['getRiceQuality']['quality']}} {{ $v['getRiceQuality']['quality_name'] }}</td>
                                            <td>{{ $v->ricemin }} - {{ $v->ricemax }}</td>
                                            <td>{{ $v->getUSDDefaultMaster->bag_size  }}</td>
                                            <td>{{ $v->getUSDDefaultMaster->bag_type  }}</td>
                                            <td style="text-transform: capitalize;">{{ $v['getRiceQuality']['quality_type'] }}</td>
                                            <td>{{ $v->transportmin }} - {{ $v->transportmax }}</td>
                                            <td>{{ round($v->dollarrate , 4) }}</td>
                                            <td>${{ $v->fobmin }} - {{ $v->fobmax }}</td>
                                            <td>{{  \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $v->created_at, 'UTC')->setTimezone('Asia/Kolkata') }}</td>
                                            <td>
                                                <a href="{{ route('delete.rice.quality' , $v->id) }}" class="btn btn-danger btn-xs">Delete</a>
                                                <a href="{{ route('edit.rice.quality' , $v->id) }}" class="btn btn-info btn-xs">Edit</a>
                                                <a href="{{ route('clone.rice.quality' , $v->id) }}" class="btn btn-info btn-xs">Clone</a> 
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                            
                            <tfoot>
                                <tr>
                                    <th>Quality</th>
                                    <th>Rice min & max </th>
                                    <th>Packing</th>
                                    <th>Packing Name</th>
                                    <th>Rice Type</th>
                                    <th>Transport</th>
                                    <th>USD Rate</th>
                                    <th>Fob</th>
                                    <th>created_at</th>
                                    <th>Action</th>
                                </tr>
                            </tfoot>
                        </table>
                        <div style="margin-top: 10px;">
                            {{ $usdPrice->onEachSide(1)->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

@endsection
@section('scripts')
    <script src="{{ asset('js/calculator.js') }}?v=2" type="text/javascript"></script>
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript">

    </script>
@endsection