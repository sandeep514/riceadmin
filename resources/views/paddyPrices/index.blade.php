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
                                <div class="form-group col-md-3">
                                    <label>State</label>
                                    <select class="form-control" name="state" id="paddy-state" required>
                                        <option value="">-- Select State --</option>
                                        @foreach($paddyStateModel as $v)
                                            <option value="{{ $v->id }}" {{ (int) old('state') === (int) $v->id ? 'selected' : '' }}>
                                                {{ $v->state }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('state')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Mandi</label>
                                    <select class="form-control" name="mandi" id="paddy-mandi" required>
                                        <option value="">-- Select Mandi --</option>
                                        @foreach($paddyMandiModel as $v)
                                            <option
                                                value="{{ $v->id }}"
                                                data-state-id="{{ $v->state_id }}"
                                                {{ (int) old('mandi') === (int) $v->id ? 'selected' : '' }}
                                            >
                                                {{ $v->mandi }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('mandi')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Date</label>
                                    <input
                                        type="date"
                                        class="form-control"
                                        name="date"
                                        value="{{ old('date', date('Y-m-d')) }}"
                                        max="{{ date('Y-m-d') }}"
                                        required
                                    >
                                    @error('date')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Crop Year</label>
                                    <select class="form-control" name="crop_year" required>
                                        @for($year = (int) date('Y'); $year >= (int) date('Y') - 5; $year--)
                                            <option value="{{ $year }}" {{ (int) old('crop_year', date('Y')) === $year ? 'selected' : '' }}>
                                                {{ $year }}
                                            </option>
                                        @endfor
                                    </select>
                                    @error('crop_year')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div>
                                <div class="form-group col-md-3">
                                    <label>Quality</label>
                                    <select class="form-control" name="quality_id" required>
                                        @foreach($quality as $k => $v)
                                            <option value="{{ $v->id }}" {{ (int) old('quality_id') === (int) $v->id ? 'selected' : '' }}>
                                                {{ $v->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Hand Cutting Price</label>
                                    <input type="text" class="form-control" name="handCutting" value="{{ old('handCutting') }}">
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Machine Cutting Price</label>
                                    <input type="text" class="form-control" name="machineCutting" value="{{ old('machineCutting') }}">
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Moisture</label>
                                    <input type="text" class="form-control" name="moisture" value="{{ old('moisture') }}">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div>
                                <div class="form-group col-md-3">
                                    <label>Total Arrival (Bags)</label>
                                    <input type="text" class="form-control" name="bags" value="{{ old('bags') }}">
                                </div>
                                <div class="form-group col-md-3">
                                    <label>Change</label>
                                    <select class="form-control" name="change">
                                        @foreach(['Stable', 'Down', 'Up'] as $change)
                                            <option value="{{ $change }}" {{ old('change', 'Stable') === $change ? 'selected' : '' }}>{{ $change }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <input type="submit" name="submit" value="Submit" class="btn btn-primary btn-sm" style="float: right">
                    </div>
                </form>
                    <div class="responsiveTabs basmatitabs">
                        <div id="myTabContent" class="tab-content" >
                            <div class="">
                                <div class="row">
                                    <div class="col-md-12 text-center">
                                        <div class="row text-left" style="margin-top: 20px;">
                                            {{-- <a href="{{ route('paddy-prices.create') }}" class="btn btn-primary mb-3">Add New Price</a> --}}
                                            <div class="col-md-12 inputs">
                                                <form method="GET" action="{{ route('list.paddy.price') }}" class="form-inline" style="margin-bottom: 15px;">
                                                    <div class="form-group" style="margin-right: 10px;">
                                                        <label for="from" style="margin-right: 6px;">From</label>
                                                        <input type="date" id="from" name="from" class="form-control" value="{{ $from ?? '' }}">
                                                    </div>
                                                    <div class="form-group" style="margin-right: 10px;">
                                                        <label for="to" style="margin-right: 6px;">To</label>
                                                        <input type="date" id="to" name="to" class="form-control" value="{{ $to ?? '' }}">
                                                    </div>
                                                    <button type="submit" class="btn btn-info btn-sm">Filter</button>
                                                    <a href="{{ route('list.paddy.price') }}" class="btn btn-default btn-sm">Clear</a>
                                                    <a href="{{ route('list.paddy.price', array_filter(['from' => $from ?? null, 'to' => $to ?? null, 'export' => 'excel'])) }}" class="btn btn-success btn-sm">
                                                        <i class="fa fa-file-excel-o"></i> Excel
                                                    </a>
                                                    <a href="{{ route('list.paddy.price', array_filter(['from' => $from ?? null, 'to' => $to ?? null, 'export' => 'pdf'])) }}" class="btn btn-danger btn-sm">
                                                        <i class="fa fa-file-pdf-o"></i> PDF
                                                    </a>
                                                </form>
                                                <table class="table table-bordered table-striped paddy-datatable" width="100%">
                                                    <thead>
                                                        <tr>
                                                            <th>ID</th>
                                                            <th>Mandi</th>
                                                            <th>State</th>
                                                            <th>Quality</th>
                                                            <th>Crop Year</th>
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
                                                                <td>{{ optional($paddyPrice->quality_rel)->name ?? '—' }}</td>
                                                                <td>{{ $paddyPrice->crop_year }}</td>
                                                                <td>{{ $paddyPrice->hand_cutting_price }}</td>
                                                                <td>{{ $paddyPrice->machine_cutting_price }}</td>
                                                                <td>{{ $paddyPrice->moisture }}</td>
                                                                <td>{{ $paddyPrice->total_arrivals }}</td>
                                                                <td>{{ $paddyPrice->change }}</td>
                                                                <td data-order="{{ optional($paddyPrice->created_at)->timestamp }}">
                                                                    {{ $paddyPrice->created_at ? $paddyPrice->created_at->format('Y-m-d') : '' }}
                                                                </td>
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
            </div>
        </section>
    </div>
@endsection

@section('javascript')
<script>
    $(function () {
        var $state = $('#paddy-state');
        var $mandi = $('#paddy-mandi');
        var mandiOptions = $mandi.find('option[data-state-id]').clone();
        var oldMandi = @json((string) old('mandi', ''));

        function loadMandis(stateId, selectedMandi) {
            $mandi.empty().append('<option value="">-- Select Mandi --</option>');

            if (!stateId) {
                $mandi.prop('disabled', true);
                return;
            }

            mandiOptions.each(function () {
                var $option = $(this);
                if (String($option.data('state-id')) === String(stateId)) {
                    $mandi.append($option.clone());
                }
            });

            $mandi.prop('disabled', false);
            if (selectedMandi && $mandi.find('option[value="' + selectedMandi + '"]').length) {
                $mandi.val(selectedMandi);
            } else {
                $mandi.val('');
            }
        }

        $state.on('change', function () {
            loadMandis($(this).val(), '');
        });

        loadMandis($state.val(), oldMandi);

        $('.paddy-datatable').DataTable({
            pageLength: 25,
            order: [[10, 'desc']],
            scrollX: true,
            columnDefs: [{ orderable: false, targets: [12] }]
        });
    });
</script>
@endsection