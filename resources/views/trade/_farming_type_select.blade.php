@php
    use App\TradeQueriesINR;
    $selectedFarming = $selectedFarming ?? old('farmingType', '');
@endphp
<div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
    {!! Form::label('Farming Type', 'Farming Type') !!}
    <select class="form-control" required name="farmingType">
        <option value=""> Select </option>
        @foreach (TradeQueriesINR::$farmingTypeWeb as $id => $label)
            <option value="{{ $id }}" {{ (string) $selectedFarming === (string) $id ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
    </select>
</div>
