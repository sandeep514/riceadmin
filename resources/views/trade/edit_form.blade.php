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
                                <input type="text" class="form-control" placeholder="SNTC Lot no" name="sntcLotNo" value="{{ $tradequeriesinr->sntcLotNo??'' }}">
                            </div>
                            <input type="hidden" name="id" value="{{ $tradequeriesinr->id }}">
                            <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('Trade For','Trade For') !!}
                                <select class="form-control" required name="tradeFor">
                                    <option value=""> Select </option>
                                        <option value="1" {{ ($tradequeriesinr->tradeFor ?? '') == 1 ? 'selected' : '' }}> App </option>
                                        <option value="2" {{ ($tradequeriesinr->tradeFor ?? '') == 2 ? 'selected' : '' }}> Web </option>
                                </select>
                            </div>
                            <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('Trade Type','Trade Type') !!}
                                <select class="form-control" required name="tradeType">
                                    <option value=""> Select </option>
                                        <option {{ ($tradequeriesinr->tradeType == 1)?'selected' : '' }} value="1"> Buy </option>
                                        <option {{ ($tradequeriesinr->tradeType == 2)?'selected' : '' }} value="2"> Sell </option>
                                        <option {{ ($tradequeriesinr->tradeType == 3)?'selected' : '' }} value="3"> Future Buying </option>
                                        <option {{ ($tradequeriesinr->tradeType == 4)?'selected' : '' }} value="4"> Future Selling </option>
                                </select>
                            </div>

                            @include('trade._farming_type_select', ['selectedFarming' => ($tradequeriesinr->farmingType ?? '')])

                            @include('trade._web_categories_grid')

                            @include('trade._web_trade_notification', ['isEdit' => true])

                            <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('Rice Category','Rice Category') !!}
                                <select class="form-control" required name="category">
                                    <option value=""> Select </option>
                                    @foreach($qualityMaster as $k => $v)
                                        <option {{ ($tradequeriesinr->quality_type == $v)?'selected' : '' }} value="{{ $v }}"> {{ strtoupper($k) }} </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('Quality','Quality') !!}
                                <select class="form-control" required name="quality">
                                    @foreach($riceName as $k => $v)
                                        <option {{ ($tradequeriesinr->quality == $v)?'selected' : '' }} value="{{ $v }}"> {{ strtoupper($k) }} </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('Rice Form','Rice Form') !!}
                                <select class="form-control" required name="riceform">
                                    @foreach($riceForm as $k => $v)
                                        <option {{ ($tradequeriesinr->qualityForm == $v)?'selected' : '' }} value="{{ $v }}"> {{ strtoupper($k) }} </option>
                                    @endforeach
                                </select>
                            </div>
                            <div  style="padding: 0px 100px" >
                                <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                    {!! Form::label('Rice Form','Rice Form (Link with live price)') !!}
                                    <select class="form-control" required name="riceformLinkWithLivePrice">
                                        <option>Select any</option>
                                        @foreach($ricefm as $k => $v)
                                            <option {{ ($tradequeriesinr->qualityFormLinkWithLivePrice == $v)?'selected' : '' }} value="{{ $v }}"> {{ strtoupper($k) }} </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                    {!! Form::label('state','State (Link with live price)') !!}
                                    <select class="form-control" required name="stateLinkWithLivePrice">
                                        <option>Select any</option>
                                        @foreach($livePricesStates as $k => $v)
                                            <option {{ ($tradequeriesinr->stateLinkWithLivePrice == $v->state)? 'selected' : '' }} value="{{ $v->state }}"> {{ $v->state }} </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('Grade','Grade') !!}
                                <select class="form-control" required name="ricegrade">
                                    @foreach($wandModel as $k => $v)
                                        <option {{ ($tradequeriesinr->grade == $v->id)?'selected' : '' }} value="{{ $v->id }}"> {{ trim(($v->getWandType?->type ?? '').' '.($v->value ?? '')) }} </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('Packing Type','Packing Type') !!}
                                <select class="form-control" required name="packingStreamType">
                                    <option {{ ($tradequeriesinr->packingStreamType == 1)?'selected': '' }} value="1">Bulk (50 | 55KG) </option>
                                    <option {{ ($tradequeriesinr->packingStreamType == 2)?'selected': '' }} value="2">Branded | Labeled: (30 - 26KG) </option>
                                </select>
                            </div>
                            <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('Packing','Packing') !!}
                                <select class="form-control" required name="ricepacking">
                                    @foreach($packingType as $k => $v)
                                        <option {{ ($tradequeriesinr->packing == $v->id)?'selected': '' }} value="{{ $v->id }}">{{ $v->size }} {{ $v->packing }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-12 trade-media-field" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('Packing','Packing Image') !!}
                                <div class="input-group">
                                    <input type="file" class="form-control trade-media-input" name="packingImage" accept="image/*">
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-default trade-media-clear" title="Clear selected file">
                                            <i class="fa fa-times"></i> Clear
                                        </button>
                                    </span>
                                </div>
                                <div class="trade-media-preview" style="margin-top:8px;display:none;"></div>
                                @if(!empty($tradequeriesinr->packing_file))
                                    <div class="trade-media-existing" style="margin-top:10px;" data-remove-name="remove_packing_file">
                                        <img src="{{ asset('uploads/'.$tradequeriesinr->packing_file) }}" alt="Packing" style="width: 200px; max-width:100%;" />
                                        <div style="margin-top:6px;">
                                            <label class="text-danger" style="font-weight:normal;">
                                                <input type="checkbox" name="remove_packing_file" value="1" class="trade-media-remove">
                                                Remove existing packing image
                                            </label>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-12 trade-media-field" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('Upload Video','Upload Video (optional)') !!}
                                <div class="input-group">
                                    <input type="file" class="form-control trade-media-input" name="video_file" accept="video/*">
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-default trade-media-clear" title="Clear selected file">
                                            <i class="fa fa-times"></i> Clear
                                        </button>
                                    </span>
                                </div>
                                <div class="trade-media-preview" style="margin-top:8px;display:none;"></div>
                                @if(!empty($tradequeriesinr->video_file))
                                    <div class="trade-media-existing" style="margin-top:10px;" data-remove-name="remove_video_file">
                                        <video src="{{ asset('uploads/'.$tradequeriesinr->video_file) }}" controls style="max-width:320px;width:100%;"></video>
                                        <div style="margin-top:6px;">
                                            <label class="text-danger" style="font-weight:normal;">
                                                <input type="checkbox" name="remove_video_file" value="1" class="trade-media-remove">
                                                Remove existing video
                                            </label>
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('Quantity','Quantity') !!}
                                <input type="text" class="form-control" required placeholder="Quantity" name="quantity" value="{{ $tradequeriesinr->quantity }}">
                            </div>
                            <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('Offer Price','Offer Price (₹)') !!}<p style="color: gray; font-size: 12px;">eg. 6300/ QTL (CD 2%)</p>
                                <input type="text" class="form-control" placeholder="Offer Price" name="price" value="{{ $tradequeriesinr->offerPrice }}">
                            </div>
                            <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('Location','Warehouse Location') !!}
                                <input type="text" class="form-control" placeholder="location" name="location" value="{{ $tradequeriesinr->location }}">
                            </div>
                            <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('Validity','Validity') !!}
                                <input type="datetime-local" id="validity" name="validity" class="form-control" value="{{ $tradequeriesinr->validDays }}">
                                {{-- <input type="text" class="form-control" placeholder="Validity ( in Days )" name="validity"> --}}
                            </div>

                            @php
                                $uncookedSlots = [
                                    ['field' => 'uncooked_file', 'remove' => 'remove_uncooked_file'],
                                    ['field' => 'uncooked_file1', 'remove' => 'remove_uncooked_file1'],
                                    ['field' => 'uncooked_file2', 'remove' => 'remove_uncooked_file2'],
                                    ['field' => 'uncooked_file3', 'remove' => 'remove_uncooked_file3'],
                                ];
                                $cookedSlots = [
                                    ['field' => 'cooked_file', 'remove' => 'remove_cooked_file'],
                                    ['field' => 'cooked_file1', 'remove' => 'remove_cooked_file1'],
                                    ['field' => 'cooked_file2', 'remove' => 'remove_cooked_file2'],
                                    ['field' => 'cooked_file3', 'remove' => 'remove_cooked_file3'],
                                ];
                            @endphp

                            <div class="row" style="padding: 0px 20px">
                                @foreach($uncookedSlots as $slot)
                                    @php $existing = $tradequeriesinr->{$slot['field']} ?? null; @endphp
                                    <div class="col-md-3 trade-media-field" style="margin-bottom: 20px;padding-left: 0">
                                        {!! Form::label('Un-Cooked image','Un-Cooked image') !!}
                                        <div class="input-group">
                                            <input type="file" class="form-control trade-media-input" name="uncookedFiles[]" accept="image/*">
                                            <span class="input-group-btn">
                                                <button type="button" class="btn btn-default trade-media-clear" title="Clear selected file">
                                                    <i class="fa fa-times"></i>
                                                </button>
                                            </span>
                                        </div>
                                        <div class="trade-media-preview" style="margin-top:8px;display:none;"></div>
                                        @if(!empty($existing))
                                            <div class="trade-media-existing" style="margin-top:8px;" data-remove-name="{{ $slot['remove'] }}">
                                                <img src="{{ asset('uploads/'.$existing) }}" alt="Uncooked" style="width: 100px; max-width:100%;" />
                                                <div style="margin-top:6px;">
                                                    <label class="text-danger" style="font-weight:normal;">
                                                        <input type="checkbox" name="{{ $slot['remove'] }}" value="1" class="trade-media-remove">
                                                        Remove
                                                    </label>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            <div class="row" style="padding: 0px 20px">
                                @foreach($cookedSlots as $slot)
                                    @php $existing = $tradequeriesinr->{$slot['field']} ?? null; @endphp
                                    <div class="col-md-3 trade-media-field" style="margin-bottom: 20px;padding-left: 0">
                                        {!! Form::label('Cooked image','Cooked image') !!}
                                        <div class="input-group">
                                            <input type="file" class="form-control trade-media-input" name="cookedFiles[]" accept="image/*">
                                            <span class="input-group-btn">
                                                <button type="button" class="btn btn-default trade-media-clear" title="Clear selected file">
                                                    <i class="fa fa-times"></i>
                                                </button>
                                            </span>
                                        </div>
                                        <div class="trade-media-preview" style="margin-top:8px;display:none;"></div>
                                        @if(!empty($existing))
                                            <div class="trade-media-existing" style="margin-top:8px;" data-remove-name="{{ $slot['remove'] }}">
                                                <img src="{{ asset('uploads/'.$existing) }}" alt="Cooked" style="width: 100px; max-width:100%;" />
                                                <div style="margin-top:6px;">
                                                    <label class="text-danger" style="font-weight:normal;">
                                                        <input type="checkbox" name="{{ $slot['remove'] }}" value="1" class="trade-media-remove">
                                                        Remove
                                                    </label>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('Crop','Crop') !!}
                                <input type="text" class="form-control" name="crop" value="{{ $tradequeriesinr->crop }}">
                            </div>
                            
                            <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('hotdeal','Hot Deal') !!}
                                <select class="form-control" name="hotdeal" id="hotdeal">
                                    <option value="0" {{ (int) ($tradequeriesinr->hotdeal ?? 0) === 0 ? 'selected' : '' }}>No</option>
                                    <option value="1" {{ (int) ($tradequeriesinr->hotdeal ?? 0) === 1 ? 'selected' : '' }}>Yes</option>
                                </select>
                            </div>
                            <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('is_new','Is New') !!}
                                <select class="form-control" name="is_new" id="is_new">
                                    <option value="0" {{ (int) ($tradequeriesinr->is_new ?? 0) === 0 ? 'selected' : '' }}>No</option>
                                    <option value="1" {{ (int) ($tradequeriesinr->is_new ?? 0) === 1 ? 'selected' : '' }}>Yes</option>
                                </select>
                            </div>
                            <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('valid_datetime_for_is_new','Valid Datetime for Is New') !!}
                                <input type="datetime-local" class="form-control" name="valid_datetime_for_is_new" id="valid_datetime_for_is_new" value="{{ $tradequeriesinr->valid_datetime_for_is_new ?? '' }}">
                            </div>

                            <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0;padding: 0px 20px;">
                                <div class="row">
                                    <h3>Spec</h3>
                                    <div class="col-md-3" style="margin-bottom: 20px;padding-left: 0">
                                        {!! Form::label('moisture','Moisture') !!}
                                        <input type="text" class="form-control" name="moisture" value="{{$tradequeriesinr->moisture}}">
                                    </div>
                                    <div class="col-md-3" style="margin-bottom: 20px;padding-left: 0">
                                        {!! Form::label('kett','Kett') !!}
                                        <input type="text" class="form-control" name="kett" value="{{$tradequeriesinr->kett}}">
                                    </div>
                                    <div class="col-md-3" style="margin-bottom: 20px;padding-left: 0">
                                        {!! Form::label('broken','Broken') !!}
                                        <input type="text" class="form-control" name="broken" value="{{$tradequeriesinr->broken}}">
                                    </div>
                                    <div class="col-md-3" style="margin-bottom: 20px;padding-left: 0">
                                        {!! Form::label('dd','DD') !!}
                                        <input type="text" class="form-control" name="dd" value="{{$tradequeriesinr->dd}}">
                                    </div>
                                    <div class="col-md-3" style="margin-bottom: 20px;padding-left: 0">
                                        {!! Form::label('admixture','Admixture') !!}
                                        <input type="text" class="form-control" name="admixture" value="{{$tradequeriesinr->admixture}}">
                                    </div>
                                    <div class="col-md-3" style="margin-bottom: 20px;padding-left: 0">
                                        {!! Form::label('elongation','Elongation') !!}
                                        <input type="text" class="form-control" name="elongation" value="{{$tradequeriesinr->elongation}}">
                                    </div>
                                    <div class="col-md-3" style="margin-bottom: 20px;padding-left: 0">
                                        {!! Form::label('Rice Size','Rice Size') !!}
                                        @php use App\TradeQueriesINR; @endphp

                                        <select class="form-control" required name="riceSize">
                                            <option value=""> Select </option>
                                            @foreach(TradeQueriesINR::$riceSize as $k => $v)
                                                <option value="{{ $k }}" {{ ($tradequeriesinr->riceSize == $k)?'selected' : '' }} > {{$v}} </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                             <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('Sold at','sold_at') !!}
                                <input type="text" class="form-control" placeholder="Sold at (Rs)" min="0" value="{{ $tradequeriesinr->sold_at }}" name="sold_at" />
                            </div>
                             <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('Additional Info','Additional Info') !!}
                                <textarea class="form-control" placeholder="Additional Info" rows="5" name="additioanlInfo">{{ $tradequeriesinr->additioanlInfo }}</textarea>
                            </div>
                             <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('personal_remarks','Personal Remarks') !!}
                                <textarea class="form-control" placeholder="Personal Remarks" rows="5" name="personal_remarks">{{ $tradequeriesinr->personal_remarks }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section('javascript')
<script type="text/javascript">

    $(document).ready(function() {
        @include('trade._web_categories_select_all_js')
        @include('trade._web_trade_notification_js')
        @include('trade._prevent_double_submit_js')
        @include('trade._media_clear_js')
        $('select[name=tradeType]').change(function(event){
            let tradeType = $('select[name=tradeType] :selected').val();
            $.ajax({
                url : 'https://snjtradelink.com/staging/public/api/get/packing/by/'+tradeType,
                success : function (res){
                    $("select[name=ricepacking]").html('');
                    $("select[name=ricepacking]").append('<option value=""> Select </option>');
                    let objectKeys = Object.keys(res.data);

                    for(let i = 0; i < Object.keys(res.data).length ; i++){
                        $("select[name=ricepacking]").append('<option value="'+res.data[i].id+'"> '+res.data[i].packing+' '+res.data[i].description+' </option>');
                    }
                },
                error: function (err){
                    console.log(err);
                }
            })
        })
        $('select[name=category]').change(function(event){
            let riceCategory = $('select[name=category] :selected').val();
            console.log(riceCategory)
            $.ajax({
                url : 'https://snjtradelink.com/staging/public/api/get/rice/qualities/'+riceCategory,
                success : function (res){
                    $("select[name=quality]").html('');
                    $("select[name=quality]").append('<option value=""> Select </option>');
                    let objectKeys = res.data;

                    for(let i = 0; i < objectKeys.length ; i++){
                        $("select[name=quality]").append('<option value="'+objectKeys[i].id+'"> '+objectKeys[i].name+' </option>');
                    }
                },
                error: function (err){
                    console.log(err);
                }
            })
        })


        $('select[name=quality]').change(function(event){
            console.log("here")
            let riceCategory = $('select[name=quality] :selected').val();
            $.ajax({
                url : 'https://snjtradelink.com/staging/public/api/get/rice/qualities/name/' + riceCategory,
                success : function (res){
                    $("select[name=riceform]").html('');
                    $("select[name=riceform]").append('<option value=""> Select </option>');

                    console.log(res.data);
                    for(let i = 0; i < res.data.length ; i++){
                        $("select[name=riceform]").append('<option value="'+res.data[i].id+'"> '+res.data[i].name+' </option>');
                    }
                },
                error: function (err){
                    console.log(err);
                }
            })            
        })


        // "get/rice/wand/" + riceNameId
        $('select[name=riceform]').change(function(event){
            console.log("here")
            let riceNameId = $('select[name=quality] :selected').val();
            $.ajax({
                url : 'https://snjtradelink.com/staging/public/api/get/rice/wand/' + riceNameId,
                success : function (res){
                    $("select[name=ricegrade]").html('');
                    $("select[name=ricegrade]").append('<option value=""> Select </option>');

                    console.log(res.data);
                    for(let i = 0; i < res.data.length ; i++){
                        $("select[name=ricegrade]").append('<option value="'+res.data[i].id+'"> '+res.data[i].get_wand_type['type']+' '+res.data[i]['value'] +'</option>');
                    }
                },
                error: function (err){
                    console.log(err);
                }
            })            
        })



    })
</script>
@endsection