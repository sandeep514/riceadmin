<div class="box-body">
    <div class="row margin-top-10">
        <div class="col-md-12">
            <div class="group-panel">
                <label class="group-title">Create Trade</label>
                <div class="group-content">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('SNTC lot no','SNTC lot no') !!}
                                <input type="text" class="form-control" placeholder="SNTC Lot no" name="sntcLotNo" value="{{ $query->sntcLotNo ?? '' }}">
                            </div>
                            <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('Trade For','Trade For') !!}
                                <select class="form-control" required name="tradeFor">
                                    <option value=""> Select </option>
                                        <option value="1" {{ ($query->tradeFor ?? '') == 1 ? 'selected' : '' }}> App </option>
                                        <option value="2" {{ ($query->tradeFor ?? '') == 2 ? 'selected' : '' }}> Web </option>
                                </select>
                            </div>
                            <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('Trade Type','Trade Type') !!}
                                <select class="form-control" required name="tradeType">
                                    <option value=""> Select </option>
                                        <option value="1" {{ (in_array("buy" , $explodeURL))?'selected' : '' }}> Buy </option>
                                        <option value="2" {{ (in_array("sell" , $explodeURL))?'selected' : '' }}> Sell </option>
                                        <option value="3" {{ (in_array("futurebuying" , $explodeURL))?'selected' : '' }}> Future Buying </option>
                                        <option value="4" {{ (in_array("futureselling" , $explodeURL))?'selected' : '' }}> Future Selling </option>
                                </select>
                            </div>

                            @if( in_array('convert' , $explodeURL) )
                                {{-- <input type="hidden" name="tradeType" value="sell" /> --}}
                                <input type="hidden" name="queryId" value="{{ $explodeURL[count($explodeURL) - 1] }}" /> 
                            @endif
                            
                            @include('trade._farming_type_select', ['selectedFarming' => ($selectedFarming ?? (($query->farming ?? '') !== '' ? (is_numeric($query->farming) ? (int)$query->farming : (strtolower((string)$query->farming) === 'conventional' ? 1 : 2)) : ''))])

                            @include('trade._web_categories_grid')

                            @include('trade._web_trade_notification', ['isEdit' => false])

                            <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('Rice Category','Rice Category') !!}
                                <select class="form-control" required name="category">
                                    <option value=""> Select </option>
                                    @foreach($qualityMaster as $k => $v)
                                        <option value="{{ $v }}" {{ ($query->quality_type ?? '') == $v ? 'selected' : '' }}> {{ strtoupper($k) }} </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('Quality','Quality') !!}
                                <select class="form-control" required name="quality">

                                </select>
                            </div>
                            <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('Rice Form','Rice Form') !!}
                                <select class="form-control" required name="riceform">

                                </select>
                            </div>
                            <div style="padding: 0px 100px;">
                                <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                    {!! Form::label('Rice Form','Rice Form (Link with live price)') !!}
                                    <select class="form-control" required name="riceformLinkWithLivePrice">
                                        <option>Select any</option>
                                    </select>
                                </div>
                                <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                    {!! Form::label('state','State (Link with live price)') !!}
                                    <select class="form-control" required name="stateLinkWithLivePrice">
                                        <option>Select any</option>
                                        @foreach($livePricesStates as $k => $v)
                                            <option value="{{ $v->state }}" {{ ($query->stateLinkWithLivePrice ?? '') == $v->state ? 'selected' : '' }}> {{ $v->state }} </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('Grade','Grade') !!}
                                <select class="form-control" required name="ricegrade">

                                </select>
                            </div>

                            @include('trade._trade_interest_users')

                            <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('Packing Type','Packing Type') !!}
                                <select class="form-control" required name="packingStreamType">
                                    <option value="1" {{ ($query->packingStreamType ?? '') == 1 ? 'selected' : '' }}>Bulk (50 | 55KG)</option>
                                    <option value="2" {{ ($query->packingStreamType ?? '') == 2 ? 'selected' : '' }}>Branded | Labeled: (30 - 26KG) </option>
                                </select>
                            </div>
                            <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('Packing','Packing') !!}
                                <select class="form-control" required name="ricepacking">
                                  @foreach($packing as $k => $v)
                                        <option value="{{ $v->id }}" {{ ($query->packing ?? '') == $v->id ? 'selected' : '' }}>{{ $v->size }} {{ $v->packing }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('Packing','Packing Image') !!}
                                <input type="file" class="form-control" name="packingImage">
                            </div>
                            <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('Upload Video','Upload Video (optional)') !!}
                                <input type="file" class="form-control" name="video_file" accept="video/*">
                            </div>
                            <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('Quantity','Quantity') !!}
                                <input type="text" class="form-control" required placeholder="Quantity" name="quantity" value="{{ $query->quantity ?? '' }}">
                            </div>
                            <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('Offer Price','Offer Price (₹)') !!}<p style="color: gray; font-size: 12px;">eg. 6300/ QTL (CD 2%)</p>
                                <input type="text" class="form-control" placeholder="Offer Price" name="price" value="{{ $query->offerPrice ?? '' }}">
                            </div>
                            <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('Location','Warehouse Location') !!}
                                <input type="text" class="form-control" placeholder="location" name="location" value="{{ $query->warehouselocation ?? '' }}">
                            </div>
                            <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('Validity','Validity') !!}
                                <input type="datetime-local" id="validity" name="validity" class="form-control" value="{{ isset($query->validDays) ? \Carbon\Carbon::parse($query->validDays)->format('Y-m-d\TH:i') : '' }}">
                                {{-- <input type="text" class="form-control" placeholder="Validity ( in Days )" name="validity"> --}}
                            </div>
                            <div>
                                <div class="col-md-3" style="margin-bottom: 20px;padding-left: 0">
                                    {!! Form::label('Un-Cooked image','Un-Cooked image') !!}
                                    <input type="file" class="form-control" name="uncookedFiles[]" >
                                </div>
                                <div class="col-md-3" style="margin-bottom: 20px;padding-left: 0">
                                    {!! Form::label('Un-Cooked image','Un-Cooked image') !!}
                                    <input type="file" class="form-control" name="uncookedFiles[]" >
                                </div>
                                <div class="col-md-3" style="margin-bottom: 20px;padding-left: 0">
                                    {!! Form::label('Un-Cooked image','Un-Cooked image') !!}
                                    <input type="file" class="form-control" name="uncookedFiles[]" >
                                </div>
                                <div class="col-md-3" style="margin-bottom: 20px;padding-left: 0">
                                    {!! Form::label('Un-Cooked image','Un-Cooked image') !!}
                                    <input type="file" class="form-control" name="uncookedFiles[]" >
                                </div>
                            </div>
                            <div>
                                <div class="col-md-3" style="margin-bottom: 20px;padding-left: 0">
                                    {!! Form::label('Cooked image','Cooked image') !!}
                                    <input type="file" class="form-control" name="cookedFiles[]">
                                </div>
                                <div class="col-md-3" style="margin-bottom: 20px;padding-left: 0">
                                    {!! Form::label('Cooked image','Cooked image') !!}
                                    <input type="file" class="form-control" name="cookedFiles[]">
                                </div>
                                <div class="col-md-3" style="margin-bottom: 20px;padding-left: 0">
                                    {!! Form::label('Cooked image','Cooked image') !!}
                                    <input type="file" class="form-control" name="cookedFiles[]">
                                </div>
                                <div class="col-md-3" style="margin-bottom: 20px;padding-left: 0">
                                    {!! Form::label('Cooked image','Cooked image') !!}
                                    <input type="file" class="form-control" name="cookedFiles[]">
                                </div>
                                
                            </div>
                            <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('Crop','Crop') !!}
                                <input type="text" class="form-control" name="crop" value="{{ $query->crop ?? '' }}">
                            </div>
                            <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('Heart','Heart Count') !!}
                                <input type="text" class="form-control" name="heart" value="{{ $query->heart ?? '' }}">
                            </div>
                            <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('hotdeal','Hot Deal') !!}
                                <select class="form-control" name="hotdeal" id="hotdeal">
                                    <option value="0" {{ ($query->hotdeal ?? 0) == 0 ? 'selected' : '' }}>No</option>
                                    <option value="1" {{ ($query->hotdeal ?? 0) == 1 ? 'selected' : '' }}>Yes</option>
                                </select>
                            </div>
                            <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('is_new','Is New') !!}
                                <select class="form-control" name="is_new" id="is_new">
                                    <option value="0" {{ ($query->is_new ?? 0) == 0 ? 'selected' : '' }}>No</option>
                                    <option value="1" {{ ($query->is_new ?? 0) == 1 ? 'selected' : '' }}>Yes</option>
                                </select>
                            </div>
                            <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('valid_datetime_for_is_new','Valid Datetime for Is New') !!}
                                <input type="datetime-local" class="form-control" name="valid_datetime_for_is_new" id="valid_datetime_for_is_new" value="{{ $query->valid_datetime_for_is_new ?? '' }}">
                            </div>

                            <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0;padding: 0px 20px;">
                                <div class="row">
                                    <h3>Spec</h3>
                                    <div class="col-md-3" style="margin-bottom: 20px;padding-left: 0">
                                        {!! Form::label('moisture','Moisture') !!}
                                        <input type="text" class="form-control" name="moisture" value="{{ $query->moisture ?? '' }}">
                                    </div>
                                    <div class="col-md-3" style="margin-bottom: 20px;padding-left: 0">
                                        {!! Form::label('kett','Kett') !!}
                                        <input type="text" class="form-control" name="kett" value="{{ $query->kett ?? '' }}">
                                    </div>
                                    <div class="col-md-3" style="margin-bottom: 20px;padding-left: 0">
                                        {!! Form::label('broken','Broken') !!}
                                        <input type="text" class="form-control" name="broken" value="{{ $query->broken ?? '' }}">
                                    </div>
                                    <div class="col-md-3" style="margin-bottom: 20px;padding-left: 0">
                                        {!! Form::label('dd','DD') !!}
                                        <input type="text" class="form-control" name="dd" value="{{ $query->dd ?? '' }}">
                                    </div>
                                    <div class="col-md-3" style="margin-bottom: 20px;padding-left: 0">
                                        {!! Form::label('admixture','Admixture') !!}
                                        <input type="text" class="form-control" name="admixture" value="{{ $query->admixture ?? '' }}">
                                    </div>
                                    <div class="col-md-3" style="margin-bottom: 20px;padding-left: 0">
                                        {!! Form::label('elongation','Elongation') !!}
                                        <input type="text" class="form-control" name="elongation" value="{{ $query->elongation ?? '' }}">
                                    </div>
                                    <div class="col-md-3" style="margin-bottom: 20px;padding-left: 0">
                                        {!! Form::label('Rice Size','Rice Size') !!}
                                        @php use App\TradeQueriesINR; @endphp

                                        <select class="form-control" required name="riceSize">
                                            <option value=""> Select </option>
                                            @foreach(TradeQueriesINR::$riceSize as $k => $v)
                                                <option value="{{ $k }}" {{ ($query->riceSize ?? '') == $k ? 'selected' : '' }}> {{$v}} </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                             <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('Additional Info','Additional Info') !!}
                                <textarea class="form-control" placeholder="Additional Info" rows="5" name="additioanlInfo">{{ $query->additional_info ?? $query->additioanlInfo ?? '' }}</textarea>
                            </div>
                             <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('personal_remark','Personal Remarks') !!}
                                <textarea class="form-control" placeholder="Personal Remarks" rows="5" name="personal_remarks">{{ $query->personal_remarks ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                    
                    
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Pre-populate AJAX dropdowns for convert mode --}}
@php
    $hasQuery = isset($query) && is_object($query);
@endphp
@if($hasQuery)
<script type="text/javascript">
    $(document).ready(function() {
        // Pre-populate quality dropdown
        let preQuality = "{{ $query->quality ?? '' }}";
        let preQualityType = "{{ $query->quality_type ?? '' }}";
        if (preQuality && preQualityType) {
            $.ajax({
                url: 'https://snjtradelink.com/staging/public/api/get/rice/qualities/' + preQualityType,
                async: false,
                success: function(res) {
                    $("select[name=quality]").html('');
                    $("select[name=quality]").append('<option value=""> Select </option>');
                    for (let i = 0; i < res.data.length; i++) {
                        let selected = (preQuality == res.data[i].id) ? "selected" : "";
                        $("select[name=quality]").append('<option value="' + res.data[i].id + '" ' + selected + '> ' + res.data[i].name + ' </option>');
                    }
                }
            });
        }

        // Pre-populate riceform dropdown
        let preRiceForm = "{{ $query->quality_form ?? $query->qualityForm ?? '' }}";
        if (preQuality) {
            $.ajax({
                url: 'https://snjtradelink.com/staging/public/api/get/rice/qualities/name/' + preQuality,
                async: false,
                success: function(res) {
                    $("select[name=riceform]").html('');
                    $("select[name=riceform]").append('<option value=""> Select </option>');
                    for (let i = 0; i < res.data.length; i++) {
                        let selected = (preRiceForm == res.data[i].id) ? "selected" : "";
                        $("select[name=riceform]").append('<option value="' + res.data[i].id + '" ' + selected + '> ' + res.data[i].name + ' </option>');
                    }
                    // Also populate riceformLinkWithLivePrice
                    $("select[name=riceformLinkWithLivePrice]").html('');
                    $("select[name=riceformLinkWithLivePrice]").append('<option>Select any</option>');
                    for (let i = 0; i < res.riceform.length; i++) {
                        $("select[name=riceformLinkWithLivePrice]").append('<option value="' + res.riceform[i].id + '"> ' + res.riceform[i].form_name + ' </option>');
                    }
                }
            });
        }

        // Pre-populate ricegrade dropdown
        let preGrade = "{{ $query->grade ?? '' }}";
        if (preQuality) {
            $.ajax({
                url: 'https://snjtradelink.com/staging/public/api/get/rice/wand/' + preQuality,
                async: false,
                success: function(res) {
                    $("select[name=ricegrade]").html('');
                    $("select[name=ricegrade]").append('<option value=""> Select </option>');
                    for (let i = 0; i < res.data.length; i++) {
                        let selected = (preGrade == res.data[i].id) ? "selected" : "";
                        $("select[name=ricegrade]").append('<option value="' + res.data[i].id + '" ' + selected + '> ' + res.data[i].get_wand_type.type + ' ' + res.data[i].value + ' </option>');
                    }
                }
            });
        }
    });
</script>
@endif
