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
                                {!! Form::label('Trade Type','Trade Type') !!}
                                <select class="form-control" required name="tradeType">
                                    <option value=""> Select </option>
                                        <option {{ ($tradequeriesinr->tradeType == 1)?'selected' : '' }} value="1"> Buy </option>
                                        <option {{ ($tradequeriesinr->tradeType == 2)?'selected' : '' }} value="2"> Sell </option>
                                </select>
                            </div>

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
                            <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('Packing','Packing Image') !!}
                                <input type="file" class="form-control" name="packingImage">
                                <img src="{{ asset('uploads/'.$tradequeriesinr->packing_file) }}" />
                            </div>
                            <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('Upload Video','Upload Video (optional)') !!}
                                <input type="file" class="form-control" name="video_file" accept="video/*">
                                @if(!empty($tradequeriesinr->video_file))
                                    <div style="margin-top:10px;">
                                        <video src="{{ asset('uploads/'.$tradequeriesinr->video_file) }}" controls style="max-width:320px;width:100%;"></video>
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

                            <div class="row" style="padding: 0px 20px">
                                <div class="col-md-3" style="margin-bottom: 20px;padding-left: 0">
                                    {!! Form::label('Un-Cooked image','Un-Cooked image') !!}
                                    <input type="file" class="form-control" name="uncookedFiles[]" >
                                    <img src="{{ asset('uploads/'.$tradequeriesinr->uncooked_file) }}" style="width: 100px" />
                                </div>
                                <div class="col-md-3" style="margin-bottom: 20px;padding-left: 0">
                                    {!! Form::label('Un-Cooked image','Un-Cooked image') !!}
                                    <input type="file" class="form-control" name="uncookedFiles[]" >
                                    <img src="{{ asset('uploads/'.$tradequeriesinr->uncooked_file1) }}" style="width: 100px" />
                                </div>
                                <div class="col-md-3" style="margin-bottom: 20px;padding-left: 0">
                                    {!! Form::label('Un-Cooked image','Un-Cooked image') !!}
                                    <input type="file" class="form-control" name="uncookedFiles[]" >
                                    <img src="{{ asset('uploads/'.$tradequeriesinr->uncooked_file2) }}" style="width: 100px" />
                                </div>
                                <div class="col-md-3" style="margin-bottom: 20px;padding-left: 0">
                                    {!! Form::label('Un-Cooked image','Un-Cooked image') !!}
                                    <input type="file" class="form-control" name="uncookedFiles[]" >
                                    <img src="{{ asset('uploads/'.$tradequeriesinr->uncooked_file3) }}" style="width: 100px" />
                                </div>
                            </div>

                            <div class="row" style="padding: 0px 20px">
                                <div class="col-md-3" style="margin-bottom: 20px;padding-left: 0">
                                    {!! Form::label('Cooked image','Cooked image') !!}
                                    <input type="file" class="form-control" name="cookedFiles[]">
                                    <img src="{{ asset('uploads/'.$tradequeriesinr->cooked_file) }}" style="width: 100px;"/>
                                </div>
                                <div class="col-md-3" style="margin-bottom: 20px;padding-left: 0">
                                    {!! Form::label('Cooked image','Cooked image') !!}
                                    <input type="file" class="form-control" name="cookedFiles[]">
                                    <img src="{{ asset('uploads/'.$tradequeriesinr->cooked_file1) }}" style="width: 100px;"/>
                                </div>
                                <div class="col-md-3" style="margin-bottom: 20px;padding-left: 0">
                                    {!! Form::label('Cooked image','Cooked image') !!}
                                    <input type="file" class="form-control" name="cookedFiles[]">
                                    <img src="{{ asset('uploads/'.$tradequeriesinr->cooked_file2) }}" style="width: 100px;"/>
                                </div>
                                <div class="col-md-3" style="margin-bottom: 20px;padding-left: 0">
                                    {!! Form::label('Cooked image','Cooked image') !!}
                                    <input type="file" class="form-control" name="cookedFiles[]">
                                    <img src="{{ asset('uploads/'.$tradequeriesinr->cooked_file3) }}" style="width: 100px;"/>
                                </div>
                                
                            </div>

                            <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('Crop','Crop') !!}
                                <input type="text" class="form-control" name="crop" value="{{ $tradequeriesinr->crop }}">
                            </div>
                            
                            <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('hotdeal','Hot Deal') !!}
                                <select class="form-control" name="hotdeal" id="hotdeal">
                                    <option value="0" {{ ($tradequeriesinr->hotdeal == 0) }}>No</option>
                                    <option value="1" {{ ($tradequeriesinr->hotdeal == 1) }}>Yes</option>
                                </select>
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