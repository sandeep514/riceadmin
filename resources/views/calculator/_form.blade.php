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
                    <option value="{{ $v->id }}" >{{ $v->quality }} {{ $v->quality_name }} ({{ $v->quality_type }})</option>
                @endforeach
            </select>
        </div>
        <div class="form-group col-md-6 ">
            {!! Form::label('rice','Cost of Rice Ex Mill Min price*') !!}
            {!! Form::text('ricemin',null,['class'=>'form-control','id'=>'ricemin' , 'required']) !!}
        </div>
        <div class="form-group col-md-6 ">
            {!! Form::label('rice','Cost of Rice Ex Mill Max price*') !!}
            {!! Form::text('ricemax',null,['class'=>'form-control','id'=>'ricemax' , 'required']) !!}
        </div>
        <div class="form-group col-md-6 ">
            {!! Form::label('rice','Transport Min price *') !!}
            {!! Form::text('portmin',null,['class'=>'form-control','id'=>'transportmin' , 'required']) !!}
        </div>
        <div class="form-group col-md-6 ">
            {!! Form::label('rice','Transport Max price *') !!}
            {!! Form::text('portmax',null,['class'=>'form-control','id'=>'transportmax' , 'required']) !!}
        </div>
        <div class="form-group col-md-6 ">
            {!! Form::label('bag','Bag Cost including Sortexing & packing labour*') !!}
            {!! Form::text('bag',1350,['class'=>'form-control','id'=>'category']) !!}
        </div>


        <div class="form-group col-md-6 ">
            {!! Form::label('charges','All Local charges( CFS Handling, B/L, THC ), Finance cost*') !!}
            {!! Form::text('charges',1735,['class'=>'form-control','id'=>'charges']) !!}
        </div>
        <div class="form-group col-md-6">
            {!! Form::label('dollar',"Today's Dollar rate *") !!}
            {!! Form::text('dollar',$dollarRate,['class'=>'form-control','id'=>'dollarrate']) !!}
        </div>

        <div class="form-group col-md-6">
            {!! Form::label('percentage','Supplier Margin+SNTC Service charge (1 - 20%)*') !!}
            <select class="form-control" name="percentage" id="percentage">
                <option value="0" selected >0</option>
                <option value="1" >1</option>
                <option value="2" >2</option>
                <option value="3" >3</option>
                <option value="4" >4</option>
                <option value="5" >5</option>
                <option value="6" >6</option>
                <option value="7" >7</option>
                <option value="8" >8</option>
                <option value="9" >9</option>
                <option value="10" >10</option>
                <option value="11" >11</option>
                <option value="12" >12</option>
                <option value="13" >13</option>
                <option value="14" >14</option>
                <option value="15" >15</option>
                <option value="16" >16</option>
                <option value="17" >17</option>
                <option value="18" >18</option>
                <option value="19" >19</option>
                <option value="20" >20</option>
            </select>
        </div>
        <div class="form-group col-md-6">
            {!! Form::label('total','Total: (in ₹) ') !!}
            <span id="total"></span>
        </div>

        <div class="form-group col-md-6">
            {!! Form::label('exchangeRate','After exchange rate: (in $)  ') !!}
            <span id="exchangeRate"></span>
        </div>

        <div class="form-group col-md-6">
            {!! Form::label('fob','FOB Prices: ') !!}
            <span id="fob"></span>
        </div>

        <div class="form-group col-md-6">
            {!! Form::label('color_status','Color') !!}
            <select class="form-control" name="color_status" id="color_status">
                <option value="1" >Green</option>
                <option value="2" >Red</option>
                <option value="3" selected >Black</option>                
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
