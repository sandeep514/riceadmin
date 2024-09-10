<div class="box-body">
    <div class="row margin-top-10">
        <div class="col-md-12">
            <div class="group-panel">
                <label class="group-title">Create Trade</label>
                <div class="group-content">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('Trade Type','Trade Type') !!}
                                <select class="form-control" required name="tradeType">
                                    <option value=""> Select </option>
                                        <option value="1"> Buy </option>
                                        <option value="2"> Sell </option>
                                </select>
                            </div>

                            <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('Rice Category','Rice Category') !!}
                                <select class="form-control" required name="category">
                                    <option value=""> Select </option>
                                    @foreach($qualityMaster as $k => $v)
                                        <option value="{{ $v }}"> {{ strtoupper($k) }} </option>
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
                            <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('Grade','Grade') !!}
                                <select class="form-control" required name="ricegrade">

                                </select>
                            </div>
                            <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('Packing','Packing') !!}
                                <select class="form-control" required name="ricepacking">
                                  {{--   @foreach($packing as $k => $v)
                                        <option value="{{ $v->id }}">{{ $v->size }} {{ $v->packing }}</option>
                                    @endforeach --}}
                                </select>
                            </div>
                            <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('Packing','Packing Image') !!}
                                <input type="file" class="form-control" name="packingImage">
                            </div>
                            <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('Quantity','Quantity') !!}
                                <input type="text" class="form-control" required placeholder="Quantity" name="quantity">
                            </div>
                            <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('Offer Price','Offer Price (₹)') !!}
                                <input type="text" class="form-control" required placeholder="Offer Price" name="price">
                            </div>
                            <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('Location','Warehouse Location') !!}
                                <input type="text" class="form-control" placeholder="location" name="location">
                            </div>
                            <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('Validity','Validity') !!}
                                <input type="datetime-local" id="validity" name="validity" class="form-control">
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
                                <input type="text" class="form-control" name="crop">
                            </div>
                            <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('Heart','Heart Count') !!}
                                <input type="text" class="form-control" name="heart">
                            </div>
                            <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('hotdeal','Hot Deal') !!}
                                <select class="form-control" name="hotdeal" id="hotdeal">
                                    <option value="0">No</option>
                                    <option value="1">Yes</option>
                                </select>
                            </div>

                            <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0;padding: 0px 20px;">
                                <div class="row">
                                    <h3>Spec</h3>
                                    <div class="col-md-3" style="margin-bottom: 20px;padding-left: 0">
                                        {!! Form::label('moisture','Moisture') !!}
                                        <input type="text" class="form-control" name="moisture">
                                    </div>
                                    <div class="col-md-3" style="margin-bottom: 20px;padding-left: 0">
                                        {!! Form::label('kett','Kett') !!}
                                        <input type="text" class="form-control" name="kett">
                                    </div>
                                    <div class="col-md-3" style="margin-bottom: 20px;padding-left: 0">
                                        {!! Form::label('broken','Broken') !!}
                                        <input type="text" class="form-control" name="broken">
                                    </div>
                                    <div class="col-md-3" style="margin-bottom: 20px;padding-left: 0">
                                        {!! Form::label('dd','DD') !!}
                                        <input type="text" class="form-control" name="dd">
                                    </div>
                                    <div class="col-md-3" style="margin-bottom: 20px;padding-left: 0">
                                        {!! Form::label('admixture','Admixture') !!}
                                        <input type="text" class="form-control" name="admixture">
                                    </div>
                                    <div class="col-md-3" style="margin-bottom: 20px;padding-left: 0">
                                        {!! Form::label('elongation','Elongation') !!}
                                        <input type="text" class="form-control" name="elongation">
                                    </div>
                                </div>
                            </div>
                             <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('Additional Info','Additional Info') !!}
                                <textarea class="form-control" placeholder="Additional Info" rows="5" name="additioanlInfo"></textarea>
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


        // 'get/seller/inr/packing'
        //     $.ajax({
        //         url : 'https://snjtradelink.com/staging/public/api/get/seller/inr/packing',
        //         success : function (res){
        //             $("select[name=ricepacking]").append('<option value=""> Select </option>');

        //             console.log(res.data);
        //             for(let i = 0; i < res.data.length ; i++){
        //                 $("select[name=ricepacking]").append('<option value="'+res.data[i].id+'"> '+res.data[i]['packing']+' </option>');
        //             }
        //         },
        //         error: function (err){
        //             console.log(err);
        //         }
        // })






    })
</script>
@endsection