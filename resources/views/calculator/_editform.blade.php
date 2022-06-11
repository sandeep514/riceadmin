<?php
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,'https://api.exchangerate.host/convert?from=USD&to=INR');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    $result = json_decode($response);
    curl_close($ch); // Close the connection

    $dollarRate = $result->result;

?>
<div class="box-body">
    <div class="row">
        <div class="form-group col-md-6">
            {!! Form::label('Rice','Rice*') !!}
            <select class="form-control" name="riceName" id="riceName">
                @foreach( $riceName as $k => $v )
                    <option {{ ( $v->id == $usdPrice->rice)? 'selected':'' }} value="{{ $v->id }}" >{{ $v->quality }} {{ $v->quality_name }} ({{ $v->quality_type }})</option>
                @endforeach
            </select>
        </div>
        <input type="hidden" name="usdPriceId" value="{{ $usdPrice->id }}">
        <div class="form-group col-md-6 ">
            {!! Form::label('rice','Cost of Rice Ex Mill Min price*') !!}
            {!! Form::text('ricemin',$usdPrice->ricemin,['class'=>'form-control','id'=>'ricemin' , 'required']) !!}
        </div>
        <div class="form-group col-md-6 ">
            {!! Form::label('rice','Cost of Rice Ex Mill Max price*') !!}
            {!! Form::text('ricemax',$usdPrice->ricemax,['class'=>'form-control','id'=>'ricemax' , 'required']) !!}
        </div>
        <div class="form-group col-md-6 ">
            {!! Form::label('rice','Transport Min price *') !!}
            {!! Form::text('portmin',$usdPrice->transportmin,['class'=>'form-control','id'=>'transportmin' , 'required']) !!}
        </div>
        <div class="form-group col-md-6 ">
            {!! Form::label('rice','Transport Max price *') !!}
            {!! Form::text('portmax',$usdPrice->transportmax,['class'=>'form-control','id'=>'transportmax' , 'required']) !!}
        </div>
        <div class="form-group col-md-6 ">
            {!! Form::label('bag','Bag Cost including Sortexing & packing labour*') !!}
            {!! Form::text('bag',$usdPrice->category,['class'=>'form-control','id'=>'category']) !!}
        </div>


        <div class="form-group col-md-6 ">
            {!! Form::label('charges','All Local charges( CFS Handling, B/L, THC ), Finance cost*') !!}
            {!! Form::text('charges',$usdPrice->charges,['class'=>'form-control','id'=>'charges']) !!}
        </div>
        <div class="form-group col-md-6">
            {!! Form::label('dollar',"Today's Dollar rate *") !!}
            {!! Form::text('dollar',$usdPrice->dollarrate,['class'=>'form-control','id'=>'dollarrate']) !!}
        </div>

        <div class="form-group col-md-6">
            {!! Form::label('percentage','Supplier Margin+SNTC Service charge (1 - 20%)*') !!}
            <select class="form-control" name="percentage" id="percentage">
                <option {{ ($usdPrice->percentageValue == 0)? 'selected': '' }} value="0" >0</option>
                <option {{ ($usdPrice->percentageValue == 1)? 'selected': '' }} value="1" >1</option>
                <option {{ ($usdPrice->percentageValue == 2)? 'selected': '' }} value="2" >2</option>
                <option {{ ($usdPrice->percentageValue == 3)? 'selected': '' }} value="3" >3</option>
                <option {{ ($usdPrice->percentageValue == 4)? 'selected': '' }} value="4" >4</option>
                <option {{ ($usdPrice->percentageValue == 5)? 'selected': '' }} value="5" >5</option>
                <option {{ ($usdPrice->percentageValue == 6)? 'selected': '' }} value="6" >6</option>
                <option {{ ($usdPrice->percentageValue == 7)? 'selected': '' }} value="7" >7</option>
                <option {{ ($usdPrice->percentageValue == 8)? 'selected': '' }} value="8" >8</option>
                <option {{ ($usdPrice->percentageValue == 9)? 'selected': '' }} value="9" >9</option>
                <option {{ ($usdPrice->percentageValue == 10)? 'selected': '' }} value="10" >10</option>
                <option {{ ($usdPrice->percentageValue == 11)? 'selected': '' }} value="11" >11</option>
                <option {{ ($usdPrice->percentageValue == 12)? 'selected': '' }} value="12" >12</option>
                <option {{ ($usdPrice->percentageValue == 13)? 'selected': '' }} value="13" >13</option>
                <option {{ ($usdPrice->percentageValue == 14)? 'selected': '' }} value="14" >14</option>
                <option {{ ($usdPrice->percentageValue == 15)? 'selected': '' }} value="15" >15</option>
                <option {{ ($usdPrice->percentageValue == 16)? 'selected': '' }} value="16" >16</option>
                <option {{ ($usdPrice->percentageValue == 17)? 'selected': '' }} value="17" >17</option>
                <option {{ ($usdPrice->percentageValue == 18)? 'selected': '' }} value="18" >18</option>
                <option {{ ($usdPrice->percentageValue == 19)? 'selected': '' }} value="19" >19</option>
                <option {{ ($usdPrice->percentageValue == 20)? 'selected': '' }} value="20" >20</option>
            </select>
        </div>
        <div class="form-group col-md-6">
            {!! Form::label('total','Total: (in ₹) ') !!}
            
            <span id="total">{{ $usdPrice->totalMin }} - {{ $usdPrice->totalMax }}</span>
        </div>

        <div class="form-group col-md-6">
            {!! Form::label('exchangeRate','After exchange rate: (in $)  ') !!}
            <span id="exchangeRate">{{ $usdPrice->exchangeRatemin }} - {{ $usdPrice->exchangeRatemax }}</span>
        </div>

        <div class="form-group col-md-6">
            {!! Form::label('fob','FOB Prices: ') !!}
            <span id="fob">{{ $usdPrice->fobmin }} - {{$usdPrice->fobmax}}</span>
        </div>

        <div class="form-group col-md-6">
            {!! Form::label('color_status','Color') !!}
            <select class="form-control" name="color_status" id="color_status">

                <option {{ ($usdPrice->color_status == 1) ? 'selected' : '' }} value="1" >Green</option>
                <option {{ ($usdPrice->color_status == 2) ? 'selected' : '' }} value="2" >Red</option>
                <option {{ ($usdPrice->color_status == 3) ? 'selected' : '' }} value="3" >Black</option>                
            </select>
        </div>

    </div>
</div>


@section('scripts')
    <script src="{{ asset('js/custom.js') }}" type="text/javascript"></script>
    <script type="text/javascript">
        $(document).ready(function(){
            // let ricemin = 0;
            // let ricemax = 0;
            // let transport = 0;
            // $('#ricemin').keyup(function(){
            //     ricemin = $('#ricemin').val();
            //     ricemax = $('#ricemax').val();
            //     if(ricemin != 0 && transport != 0){
            //         let category = $('#category').val();
            //         let charge = $('#charges').val();
            //         let total = parseFloat(parseFloat(ricemin)+parseFloat(category)+parseFloat(charge)+parseFloat(transport)).toFixed(2);
            //         let afterExchangeRate = parseFloat((parseFloat(ricemin)+parseFloat(category)+parseFloat(charge)+parseFloat(transport) ) / $('#dollarrate').val()).toFixed(2);
            //         $('#total').html(total)
            //         $('#exchangeRate').html(afterExchangeRate)
            //     }
            // })
            // $('#transport').keyup(function(){
            //     transport = $('#transport').val();
            //     if(ricemin != 0 && transport != 0){
            //         let category = $('#category').val();
            //         let charge = $('#charges').val();
            //         let total = parseFloat(parseFloat(ricemin)+parseFloat(category)+parseFloat(charge)+parseFloat(transport)).toFixed(2);
            //         let afterExchangeRate = parseFloat((parseFloat(ricemin)+parseFloat(category)+parseFloat(charge)+parseFloat(transport) ) / $('#dollarrate').val()).toFixed(2);
            //         $('#total').html(total)
            //         $('#exchangeRate').html(afterExchangeRate)
            //     }
            // })
            // $('#dollarrate').keyup(function(){
            //     if(ricemin != 0 && transport != 0){
            //         let category = $('#category').val();
            //         let charge = $('#charges').val();
            //         let total = parseFloat(parseFloat(ricemin)+parseFloat(category)+parseFloat(charge)+parseFloat(transport)).toFixed(2);
            //         let afterExchangeRate = parseFloat((parseFloat(ricemin)+parseFloat(category)+parseFloat(charge)+parseFloat(transport) ) / $('#dollarrate').val()).toFixed(2);
            //         $('#total').html(total)
            //         $('#exchangeRate').html(afterExchangeRate)
            //     }  
            // })
            // $('#percentage').change(function(){
            //     let exchangeRate = $('#exchangeRate').html();
            //     let percentageValue = $('#percentage').val();
            //     $('#fob').html('$'+Math.round((parseFloat(((exchangeRate*percentageValue)/100 ).toFixed(2)) + parseFloat(parseFloat(exchangeRate).toFixed(2))).toFixed(2)));
            // })

            $('.calculate').click(function(e){
                e.preventDefault();
                e.stopPropagation() 
                let ricemin = $('#ricemin').val();
                let ricemax = $('#ricemax').val();
                let transportmin = $('#transportmin').val();
                let transportmax = $('#transportmax').val();
                let category = $('#category').val();
                let charges = $('#charges').val();
                let dollarrate = $('#dollarrate').val();

                let total = $('#total').html();
                let exchangeRate = $('#exchangeRate').html();
                let percentageValue = $('#percentage').val();

                if(ricemin == ''){
                    ricemin = 0
                }
                if(ricemax == ''){
                    ricemax = 0
                }
                if(transportmin == ''){
                    transportmin = 0
                }
                if(transportmax == ''){
                    transportmax = 0
                }
                if( ricemax < ricemin ){
                    alert('Rice max price should be greater than Rice min price.');
                    return false;
                }
                if( transportmax < transportmin ){
                    alert('Transport max price should be greater than Transport min price.');
                    return false;
                }

                if( ricemin != 0 && ricemax != 0 && transportmin != '' && transportmax != '' && category != '' && transportmin != ''&& transportmax != '' && dollarrate != '' && percentageValue != '' && charges != ''){
                   
                    let totalMin = parseFloat(parseFloat(ricemin)+parseFloat(category)+parseFloat(transportmin)+parseFloat(charges)).toFixed(2);
                    let totalMax = parseFloat(parseFloat(ricemax)+parseFloat(category)+parseFloat(transportmax)+parseFloat(charges)).toFixed(2);
                    let exchangeRatemin = parseFloat(totalMin / $('#dollarrate').val()).toFixed(2);

                    let exchangeRatemax = parseFloat(totalMax / $('#dollarrate').val()).toFixed(2);
                    
                    let Fobmin = Math.round((parseFloat(((exchangeRatemin*percentageValue)/100 ).toFixed(2)) + parseFloat(parseFloat(exchangeRatemin).toFixed(2))).toFixed(2));
                    let Fobmax = Math.round((parseFloat(((exchangeRatemax*percentageValue)/100 ).toFixed(2)) + parseFloat(parseFloat(exchangeRatemax).toFixed(2))).toFixed(2));

                    $('#total').html('₹'+Math.round(totalMin)+' - ₹'+Math.round(totalMax));
                    $('#exchangeRate').html('$'+Math.round(exchangeRatemin)+' - $'+Math.round(exchangeRatemax));
                    $('#fob').html('$'+Math.round(Fobmin)+' - $'+Math.round(Fobmax) );
                }

                if( ricemin != 0 && ricemax == 0 && transportmin != '' && transportmax != '' && category != '' && transportmin != ''&& transportmax != '' && dollarrate != '' && percentageValue != ''){
                   
                    let totalMin = parseFloat(parseFloat(ricemin)+parseFloat(category)+parseFloat(transportmin)+parseFloat(charges)).toFixed(2);
                    let totalMax = 0;
                    let exchangeRatemin = parseFloat((totalMin / $('#dollarrate').val())).toFixed(2);

                    let exchangeRatemax = 0;
                    let Fobmin = Math.round((parseFloat(((exchangeRatemin*percentageValue)/100 ).toFixed(2)) + parseFloat(parseFloat(exchangeRatemin).toFixed(2))).toFixed(2));
                    let Fobmax = Math.round((parseFloat(((exchangeRatemax*percentageValue)/100 ).toFixed(2)) + parseFloat(parseFloat(exchangeRatemax).toFixed(2))).toFixed(2));

                    $('#total').html('₹'+Math.round(totalMin)+' - ₹'+Math.round(totalMax));
                    $('#exchangeRate').html('$'+Math.round(exchangeRatemin)+' - $'+Math.round(exchangeRatemax));
                    $('#fob').html('$'+Math.round(Fobmin)+' - $'+Math.round(Fobmax) );
                }

                if( ricemin == 0 && ricemax != 0 && transportmin != '' && transportmax != '' && category != '' && transportmin != ''&& transportmax != '' && dollarrate != '' && percentageValue != ''){
                   
                    let totalMin = 0;
                    let totalMax = parseFloat(parseFloat(ricemax)+parseFloat(category)+parseFloat(transportmax)+parseFloat(charges)).toFixed(2);
                    let exchangeRatemin = 0;
                    let exchangeRatemax = parseFloat(totalMax / $('#dollarrate').val()).toFixed(2);

                    let Fobmin = Math.round((parseFloat(((exchangeRatemin*percentageValue)/100 ).toFixed(2)) + parseFloat(parseFloat(exchangeRatemin).toFixed(2))).toFixed(2));
                    let Fobmax = Math.round((parseFloat(((exchangeRatemax*percentageValue)/100 ).toFixed(2)) + parseFloat(parseFloat(exchangeRatemax).toFixed(2))).toFixed(2));

                    $('#total').html('₹'+Math.round(totalMin)+' - ₹'+Math.round(totalMax));
                    $('#exchangeRate').html('$'+Math.round(exchangeRatemin)+' - $'+Math.round(exchangeRatemax));
                    $('#fob').html('$'+Math.round(Fobmin)+' - $'+Math.round(Fobmax) );
                }


                // if(ricemin != '' && ricemax == '' && transportmin != '' && transportmax != '' && category != '' && charges != '' && transport != '' && total != '' && exchangeRate != '' && dollarrate != '' && percentage != '' && ricemin != 0 && category != 0 && charges != 0 && transport != 0 && total != 0 && exchangeRate != 0 && dollarrate != 0 && percentage != 0){

                // }

                // if(ricemin != '' && ricemax != '' && transportmin != '' && transportmax != '' && category != '' && charges != '' && transport != '' && total != '' && exchangeRate != '' && dollarrate != '' && percentage != '' && ricemin != 0 && category != 0 && charges != 0 && transport != 0 && total != 0 && exchangeRate != 0 && dollarrate != 0 && percentage != 0){
                //     $('#fob').html('$'+Math.round((parseFloat(((exchangeRate*percentageValue)/100 ).toFixed(2)) + parseFloat(parseFloat(exchangeRate).toFixed(2))).toFixed(2)));
                //     console.log(('$'+Math.round((parseFloat(((exchangeRate*percentageValue)/100 ).toFixed(2)) + parseFloat(parseFloat(exchangeRate).toFixed(2))).toFixed(2))));
                // }else{
                //     alert('Please alert all required fields');
                // }
            })
        })
    </script>
@endsection
