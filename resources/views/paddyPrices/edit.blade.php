@extends('layouts.main')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                Edit Paddy Price
                <small>#{{ $paddyPrice->id }}</small>
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
                <li><a href="{{ route('list.paddy.price') }}">Paddy Prices</a></li>
                <li class="active">Edit</li>
            </ol>
        </section>

        <section class="content">
            <div class="row">
                <div class="col-md-12">
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">Paddy Price Details</h3>
                        </div>
                        <form method="POST" action="{{ route('update.paddy.price', $paddyPrice->id) }}">
                            @csrf
                            @method('PUT')
                            @if(!empty($from))
                                <input type="hidden" name="from" value="{{ $from }}">
                            @endif
                            @if(!empty($to))
                                <input type="hidden" name="to" value="{{ $to }}">
                            @endif

                            <div class="box-body">
                                <div class="row">
                                    <div class="form-group col-md-3">
                                        <label>State</label>
                                        <select class="form-control" name="state" id="paddy-state" required>
                                            <option value="">-- Select State --</option>
                                            @foreach($paddyStateModel as $v)
                                                <option value="{{ $v->id }}" {{ (int) old('state', $paddyPrice->state) === (int) $v->id ? 'selected' : '' }}>
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
                                                    {{ (int) old('mandi', $paddyPrice->mandi) === (int) $v->id ? 'selected' : '' }}
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
                                            value="{{ old('date', optional($paddyPrice->created_at)->format('Y-m-d')) }}"
                                            max="{{ date('Y-m-d') }}"
                                            required
                                        >
                                        @error('date')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Crop Year</label>
                                        @php
                                            $selectedCropYear = (int) old('crop_year', $paddyPrice->crop_year);
                                            $cropYearStart = (int) date('Y');
                                            $cropYearEnd = min((int) date('Y') - 5, $selectedCropYear ?: (int) date('Y'));
                                        @endphp
                                        <select class="form-control" name="crop_year" required>
                                            @for($year = $cropYearStart; $year >= $cropYearEnd; $year--)
                                                <option value="{{ $year }}" {{ $selectedCropYear === $year ? 'selected' : '' }}>
                                                    {{ $year }}
                                                </option>
                                            @endfor
                                        </select>
                                        @error('crop_year')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="form-group col-md-3">
                                        <label>Quality</label>
                                        <select class="form-control" name="quality_id" required>
                                            <option value="">-- Select Quality --</option>
                                            @foreach($quality as $v)
                                                <option value="{{ $v->id }}" {{ (int) old('quality_id', $paddyPrice->quality_id) === (int) $v->id ? 'selected' : '' }}>
                                                    {{ $v->type_label }} - {{ $v->quality }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('quality_id')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Hand Cutting Price</label>
                                        <input type="text" class="form-control" name="hand_cutting_price" value="{{ old('hand_cutting_price', $paddyPrice->hand_cutting_price) }}">
                                        @error('hand_cutting_price')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Machine Cutting Price</label>
                                        <input type="text" class="form-control" name="machine_cutting_price" value="{{ old('machine_cutting_price', $paddyPrice->machine_cutting_price) }}">
                                        @error('machine_cutting_price')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Moisture</label>
                                        <input type="text" class="form-control" name="moisture" value="{{ old('moisture', $paddyPrice->moisture) }}">
                                        @error('moisture')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="form-group col-md-3">
                                        <label>Total Arrival (Bags)</label>
                                        <input type="text" class="form-control" name="total_arrivals" value="{{ old('total_arrivals', $paddyPrice->total_arrivals) }}">
                                        @error('total_arrivals')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Change</label>
                                        @php $selectedChange = ucfirst(strtolower((string) old('change', $paddyPrice->change))); @endphp
                                        <select class="form-control" name="change">
                                            @foreach(['Stable', 'Down', 'Up'] as $change)
                                                <option value="{{ $change }}" {{ $selectedChange === $change ? 'selected' : '' }}>{{ $change }}</option>
                                            @endforeach
                                        </select>
                                        @error('change')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="form-group col-md-3">
                                        <label>Status</label>
                                        <select class="form-control" name="status" required>
                                            <option value="1" {{ (string) old('status', $paddyPrice->status) === '1' ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ (string) old('status', $paddyPrice->status) === '0' ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                        @error('status')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="box-footer">
                                <button type="submit" class="btn btn-primary">Update</button>
                                <a
                                    href="{{ route('list.paddy.price', array_filter(['from' => $from ?? null, 'to' => $to ?? null])) }}"
                                    class="btn btn-default"
                                >
                                    Cancel
                                </a>
                            </div>
                        </form>
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
        var oldMandi = @json((string) old('mandi', $paddyPrice->mandi));

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
    });
</script>
@endsection
