@extends('layouts.main')

@section('content')
<style>
    .nonbasmatitabs .nav>li>a {
        padding: 10px 11px;
    }    
    .basmatitabs .nav>li>a {
        padding: 10px 11px;
    }
</style>
    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                Paddy Prices
                <small>List</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="{{ route('list.paddy.price') }}">Paddy Prices</a></li>
                <li class="active">List</li>
            </ol>
        </section>

        <section class="content">
            <div class="box-body">
                <form method="POST" action="{{ route('save.paddy.price') }}">
                    
                    @csrf
                    <div class="row">
                        <div class="col-md-12">
                            <div>
                                {{-- <div class="form-group col-md-3">
                                    <label>State</label>
                                    <select class="form-control" name="state">
                                        @foreach($paddyStateModel as $k => $v)
                                            <option value="{{ $v->id }}">{{ $v->state }}</option>
                                        @endforeach
                                    </select>
                                </div> --}}
                                <div class="form-group col-md-3">
                                    <label>Mandi</label>
                                    <select class="form-control" name="mandi">
                                        @foreach($paddyMandiModel as $k => $v)
                                            <option value="{{ $v->id }}">{{ $v->mandi }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Quality</label>
                                    <select class="form-control" name="quality_id">
                                        @foreach($quality as $k => $v)
                                            <option value="{{ $v->id }}">{{ $v->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div>
                                <div class="form-group col-md-3">
                                    <label>Hand Cutting Price</label>
                                    <input type="text" class="form-control" name="handCutting">
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Machine Cutting Price</label>
                                    <input type="text" class="form-control" name="machineCutting">
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Moisture</label>
                                    <input type="text" class="form-control" name="moisture">
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Total Arrival (Bags)</label>
                                    <input type="text" class="form-control" name="bags">
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Moisture</label>
                                    <select class="form-control" name="change">
                                        <option>Stable</option>
                                        <option>Down</option>
                                        <option>Up</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <input type="submit" name="submit" value="Submit" class="btn btn-primary btn-sm" style="float: right">
                    </div>
                    <div class="responsiveTabs basmatitabs">
                        <div id="myTabContent" class="tab-content" >
                            <div class="">
                                <div class="row">
                                    <div class="col-md-12 text-center">
                                        <div class="row text-left" style="margin-top: 20px;">
                                            {{-- <a href="{{ route('paddy-prices.create') }}" class="btn btn-primary mb-3">Add New Price</a> --}}
                                            <div class="col-md-12 inputs">
                                                
                                                <table class="table">
                                                    <thead>
                                                        <tr>
                                                            <th>ID</th>
                                                            <th>Mandi</th>
                                                            <th>State</th>
                                                            <th>Quality ID</th>
                                                            <th>Hand Cutting Price</th>
                                                            <th>Machine Cutting Price</th>
                                                            <th>Moisture</th>
                                                            <th>Total Arrivals</th>
                                                            <th>Change</th>
                                                            <th>Created At</th>
                                                            <th>Status</th>
                                                            <th>Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($paddyPrices as $paddyPrice)
                                                            <tr>
                                                                <td>{{ $paddyPrice->id }}</td>
                                                                <td>{{ $paddyPrice->getMandi_rel->mandi }}</td>
                                                                <td>{{ $paddyPrice->getState_rel->state }}</td>
                                                                <td>{{ $paddyPrice->quality_rel->name }}</td>
                                                                <td>{{ $paddyPrice->hand_cutting_price }}</td>
                                                                <td>{{ $paddyPrice->machine_cutting_price }}</td>
                                                                <td>{{ $paddyPrice->moisture }}</td>
                                                                <td>{{ $paddyPrice->total_arrivals }}</td>
                                                                <td>{{ $paddyPrice->change }}</td>
                                                                <td>{{ $paddyPrice->created_at }}</td>
                                                                <td>{{ $paddyPrice->status ? 'Active' : 'Inactive' }}</td>
                                                                <td>
                                                                    {{-- <a href="{{ route('paddy-prices.show', $paddyPrice) }}" class="btn btn-info btn-sm">View</a>
                                                                    <a href="{{ route('paddy-prices.edit', $paddyPrice) }}" class="btn btn-warning btn-sm">Edit</a>
                                                                    <form action="{{ route('paddy-prices.destroy', $paddyPrice) }}" method="POST" style="display:inline;">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</button>
                                                                    </form> --}}
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
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('js/live-price.js?ref='.rand(1111,9999)) }}"></script>
@endsection