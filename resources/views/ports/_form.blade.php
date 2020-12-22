<div class="box-body">
    <div class="row">
        <div class="col-md-4">
            {!! Form::label('date','Date') !!}
            {!! Form::text('date',request()->date,['class'=>'form-control datepicker','autocomplete'=>'off']) !!}
        </div>
        <div class="col-md-6">
            {!! Form::button('View Ports',['class'=>'btn btn-primary view_ports','style'=>'margin-top: 4%;']) !!}
            @if(request()->date != null)
                {!! Form::button('Clear',['class'=>'btn btn-danger clear_ports','style'=>'margin-top: 4%;']) !!}
            @endif
        </div>
    </div>
    <div class="row margin-top-10">
        <div class="col-md-12">
            <div class="group-panel">
                <label class="group-title">Punjab</label>
                <div class="group-content">
                    <div class="row">
                        @php
                            $mundra = null;
                            $gahndidam = null;
                            $nawaShehar = null;
                            $banglore = null;
                            $coimbatore = null;
                            $mumbai = null;
                            $delhi = null;
                            $hyderabad = null;
                            $jnpt_port = null;
                            
                            // whereIn('route',['mundra_port','gandhidham_gujarat','nhavasheva_mumbai'])
                            
                            $punjabPrices = $prices->where('state','punjab');
                            if(!$punjabPrices->isEmpty()){
                                $mundra = $punjabPrices->where('route','mundra_port')->first();
                                if($mundra != null){
                                    $mundra = $mundra->price;
                                }
                                $banglore = $punjabPrices->where('route','banglore')->first();
                                if($banglore != null){
                                    $banglore = $banglore->price;
                                }
                                $coimbatore = $punjabPrices->where('route','coimbatore')->first();
                                if($coimbatore != null){
                                    $coimbatore = $coimbatore->price;
                                }
                                $delhi = $punjabPrices->where('route','delhi')->first();
                                if($delhi != null){
                                    $delhi = $delhi->price;
                                }
                                $hyderabad = $punjabPrices->where('route','hyderabad')->first();
                                if($hyderabad != null){
                                    $hyderabad = $hyderabad->price;
                                }
                                $gahndidam = $punjabPrices->where('route','kandla_port')->first();
                                if($gahndidam != null){
                                    $gahndidam = $gahndidam->price;
                                }
                                $mumbai = $punjabPrices->where('route','mumbai')->first();
                                if($mumbai != null){
                                    $mumbai = $mumbai->price;
                                }
                                
                                $nawaShehar = $punjabPrices->where('route','nhavasheva_mumbai')->first();
                                if($nawaShehar != null){
                                    $nawaShehar = $nawaShehar->price;
                                }
                                
                                $jnpt_port = $punjabPrices->where('route','JNPT_port')->first();
                                if($jnpt_port != null){
                                    $jnpt_port = $jnpt_port->price;
                                }
                            }
                        @endphp
                        <!--<div class="col-md-4">-->
                        <!--    {!! Form::label('banglore','Banglore') !!}-->
                        <!--    {!! Form::text('punjab[banglore]',$banglore,['class'=>'form-control']) !!}-->
                        <!--</div>-->
                        <!--<div class="col-md-4">-->
                        <!--    {!! Form::label('coimbatore','Coimbatore') !!}-->
                        <!--    {!! Form::text('punjab[coimbatore]',$coimbatore,['class'=>'form-control']) !!}-->
                        <!--</div>-->
                        <!--<div class="col-md-4">-->
                        <!--    {!! Form::label('delhi','Delhi') !!}-->
                        <!--    {!! Form::text('punjab[delhi]',$delhi,['class'=>'form-control']) !!}-->
                        <!--</div>-->
                        <!--<div class="col-md-4">-->
                        <!--    {!! Form::label('hyderabad','Hyderabad') !!}-->
                        <!--    {!! Form::text('punjab[hyderabad]',$hyderabad,['class'=>'form-control']) !!}-->
                        <!--</div>-->
                        <div class="col-md-4">
                            {!! Form::label('kandla_port','Kandla Port') !!}
                            {!! Form::text('punjab[kandla_port]',$gahndidam,['class'=>'form-control']) !!}
                        </div>
                        <div class="col-md-4">
                            {!! Form::label('mundra_port','Mundra Port') !!}
                            {!! Form::text('punjab[mundra_port]',$mundra,['class'=>'form-control']) !!}
                        </div>
                        <div class="col-md-4">
                            {!! Form::label('jnpt_port','JNPT PORT') !!}
                            {!! Form::text('punjab[JNPT_port]',$jnpt_port,['class'=>'form-control']) !!}
                        </div>
                        <!--<div class="col-md-4">-->
                        <!--    {!! Form::label('mumbai','Mumbai') !!}-->
                        <!--    {!! Form::text('punjab[mumbai]',$mumbai,['class'=>'form-control']) !!}-->
                        <!--</div>-->
                        
                        <!--<div class="col-md-4">-->
                        <!--    {!! Form::label('nhavasheva_mumbai','Navashehar Mumbai') !!}-->
                        <!--    {!! Form::text('punjab[nhavasheva_mumbai]',$nawaShehar,['class'=>'form-control']) !!}-->
                        <!--</div>-->
                    </div>
                </div>
            </div>
            <div class="group-panel">
                <label class="group-title">Haryana</label>
                <div class="group-content">
                    <div class="row">
                        @php
                            $mundra = null;
                            $gahndidam = null;
                            $nawaShehar = null;
                            $banglore = null;
                            $coimbatore = null;
                            $hyderabad = null;
                            $mumbai = null;
                            $jnpt_port = null;
                            $kandla_port = null;
                            
                            $haryanaPrice = $prices->where('state','haryana');
                            if(!$haryanaPrice->isEmpty()){
                                $banglore = $haryanaPrice->where('route','banglore')->first();
                                if($banglore != null){
                                    $banglore = $banglore->price;
                                }
                                $coimbatore = $haryanaPrice->where('route','coimbatore')->first();
                                if($coimbatore != null){
                                    $coimbatore = $coimbatore->price;
                                }
                                $hyderabad = $haryanaPrice->where('route','hyderabad')->first();
                                if($hyderabad != null){
                                    $hyderabad = $hyderabad->price;
                                }
                                
                                $mumbai = $haryanaPrice->where('route','mumbai')->first();
                                if($mumbai != null){
                                    $mumbai = $mumbai->price;
                                }
                                
                                $mundra = $haryanaPrice->where('route','mundra_port')->first();
                                if($mundra != null){
                                    $mundra = $mundra->price;
                                }
                                
                                $jnpt_port = $haryanaPrice->where('route','JNPT_port')->first();
                                if($jnpt_port != null){
                                    $jnpt_port = $jnpt_port->price;
                                }
                                
                                $kandla_port = $haryanaPrice->where('route','kandla_port')->first();
                                if($kandla_port != null){
                                    $kandla_port = $kandla_port->price;
                                }
                                $nawaShehar = $haryanaPrice->where('route','nhavasheva_mumbai')->first();
                                if($nawaShehar != null){
                                    $nawaShehar = $nawaShehar->price;
                                }
                                
                            }
                        @endphp
                        <!--<div class="col-md-4">-->
                        <!--    {!! Form::label('banglore','Banglore') !!}-->
                        <!--    {!! Form::text('haryana[banglore]',$banglore,['class'=>'form-control']) !!}-->
                        <!--</div>-->
                        <!--<div class="col-md-4">-->
                        <!--    {!! Form::label('coimbatore','Coimbatore') !!}-->
                        <!--    {!! Form::text('haryana[coimbatore]',$coimbatore,['class'=>'form-control']) !!}-->
                        <!--</div>-->
                        <!--<div class="col-md-4">-->
                        <!--    {!! Form::label('hyderabad','Hyderabad') !!}-->
                        <!--    {!! Form::text('haryana[hyderabad]',$hyderabad,['class'=>'form-control']) !!}-->
                        <!--</div>-->
                        <div class="col-md-4">
                            {!! Form::label('kandla_port','Gandhidham / Kandla Port') !!}
                            {!! Form::text('haryana[kandla_port]',$kandla_port,['class'=>'form-control']) !!}
                        </div>
                        <!--<div class="col-md-4">-->
                        <!--    {!! Form::label('mumbai','Mumbai') !!}-->
                        <!--    {!! Form::text('haryana[mumbai]',$mumbai,['class'=>'form-control']) !!}-->
                        <!--</div>-->
                        <div class="col-md-4">
                            {!! Form::label('mundra_port','Mundra Port') !!}
                            {!! Form::text('haryana[mundra_port]',$mundra,['class'=>'form-control']) !!}
                        </div>
                        <div class="col-md-4">
                            {!! Form::label('jnpt_port','JNPT PORT') !!}
                            {!! Form::text('haryana[JNPT_port]',$jnpt_port,['class'=>'form-control']) !!}
                        </div>
                        
                        <!--<div class="col-md-4">-->
                        <!--    {!! Form::label('gandhidham_gujarat','Kandla/Gandhidham-Gujarat') !!}-->
                        <!--    {!! Form::text('haryana[gandhidham_gujarat]',$gahndidam,['class'=>'form-control']) !!}-->
                        <!--</div>-->
                        <!--<div class="col-md-4">-->
                        <!--    {!! Form::label('nhavasheva_mumbai','Nhava Sheva-Mumbai') !!}-->
                        <!--    {!! Form::text('haryana[nhavasheva_mumbai]',$nawaShehar,['class'=>'form-control']) !!}-->
                        <!--</div>-->
                    </div>
                </div>
            </div>
            <div class="group-panel">
                <label class="group-title">Rajasthan</label>
                <div class="group-content">
                    <div class="row">
                        @php
                            $mundra = null;
                            $gahndidam = null;
                            $nawaShehar = null;
                            $kandla_port = null;
                            
                            $bundiPrice = $prices->where('state','bundi')->whereIn('route',['mundra_port','kandla_port','nhavasheva_mumbai']);
                            if(!$bundiPrice->isEmpty()){
                                $mundra = $bundiPrice->where('route','mundra_port')->first();
                                if($mundra != null){
                                    $mundra = $mundra->price;
                                }
                                
                                $kandla_port = $bundiPrice->where('route','kandla_port')->first();
                                if($kandla_port != null){
                                    $kandla_port = $kandla_port->price;
                                }
                                $nawaShehar = $bundiPrice->where('route','nhavasheva_mumbai')->first();
                                if($nawaShehar != null){
                                    $nawaShehar = $nawaShehar->price;
                                }
                            }
                        @endphp
                        <div class="col-md-4">
                            {!! Form::label('mundra_port','Mundra Port') !!}
                            {!! Form::text('bundi[mundra_port]',$mundra,['class'=>'form-control']) !!}
                        </div>
                        <div class="col-md-4">
                            {!! Form::label('kandla_port','Kandla Port') !!}
                            {!! Form::text('bundi[kandla_port]',$kandla_port,['class'=>'form-control']) !!}
                        </div>
                        <!--<div class="col-md-4">-->
                        <!--    {!! Form::label('nhavasheva_mumbai','Nhava Sheva-Mumbai') !!}-->
                        <!--    {!! Form::text('bundi[nhavasheva_mumbai]',$nawaShehar,['class'=>'form-control']) !!}-->
                        <!--</div>-->
                    </div>
                </div>
            </div>

            <div class="group-panel">
                <label class="group-title">Madhya Pradesh</label>
                <div class="group-content">
                    <div class="row">
                        @php
                            $mundra = null;
                            $gahndidam = null;
                            $nawaShehar = null;
                            $kandla_port = null;
                            
                            $madhyaPradeshPrice = $prices->where('state','madhya_pradesh')->whereIn('route',['mundra_port','kandla_port','gandhidham_gujarat','nhavasheva_mumbai']);
                            if(!$madhyaPradeshPrice->isEmpty()){
                                $mundra = $madhyaPradeshPrice->where('route','mundra_port')->first();
                                if($mundra != null){
                                    $mundra = $mundra->price;
                                }
                                $kandla_port = $madhyaPradeshPrice->where('route','kandla_port')->first();
                                if($kandla_port != null){
                                    $kandla_port = $kandla_port->price;
                                }
                                $nawaShehar = $madhyaPradeshPrice->where('route','nhavasheva_mumbai')->first();
                                if($nawaShehar != null){
                                    $nawaShehar = $nawaShehar->price;
                                }
                            }
                        @endphp
                        
                        <div class="col-md-4">
                            {!! Form::label('mundra_port','Mundra Port') !!}
                            {!! Form::text('madhya_pradesh[mundra_port]',$mundra,['class'=>'form-control']) !!}
                         </div>
                        <div class="col-md-4">
                            {!! Form::label('kandla_port','Kandla Port') !!}
                            {!! Form::text('madhya_pradesh[kandla_port]',$kandla_port,['class'=>'form-control']) !!}
                        </div>
                        <!--<div class="col-md-4">-->
                        <!--    {!! Form::label('nhavasheva_mumbai','Nhava Sheva-Mumbai') !!}-->
                        <!--    {!! Form::text('madhya_pradesh[nhavasheva_mumbai]',$nawaShehar,['class'=>'form-control']) !!}-->
                        <!--</div>-->
                    </div>
                </div>
            </div>
            <div class="group-panel">
                <label class="group-title">Gujarat</label>
                <div class="group-content">
                    <div class="row">
                        @php
                            $mundra = null;
                            $gahndidam = null;
                            $nawaShehar = null;
                            $gujaratPrice = $prices->where('state','gujarat')->whereIn('route',['mundra_port','kandla_port','gandhidham_gujarat','nhavasheva_mumbai']);
                            if(!$gujaratPrice->isEmpty()){
                                $mundra = $gujaratPrice->where('route','mundra_port')->first();
                                if($mundra != null){
                                    $mundra = $mundra->price;
                                }
                                $kandla_port = $gujaratPrice->where('route','kandla_port')->first();
                                if($kandla_port != null){
                                    $kandla_port = $kandla_port->price;
                                }
                                $nawaShehar = $gujaratPrice->where('route','nhavasheva_mumbai')->first();
                                if($nawaShehar != null){
                                    $nawaShehar = $nawaShehar->price;
                                }
                            }
                        @endphp
                        <div class="col-md-4">
                            {!! Form::label('gujarat[mundra_port]','MUNDRA PORT') !!}
                            {!! Form::text('gujarat[mundra_port]',$mundra,['class'=>'form-control']) !!}
                        </div>
                        <div class="col-md-4">
                            {!! Form::label('kandla_port','KANDLA PORT') !!}
                            {!! Form::text('gujarat[kandla_port]',$kandla_port,['class'=>'form-control']) !!}
                        </div>
                        <!--<div class="col-md-4">-->
                        <!--    {!! Form::label('nhavasheva_mumbai','Nhava Sheva-Mumbai') !!}-->
                        <!--    {!! Form::text('gujarat[nhavasheva_mumbai]',$nawaShehar,['class'=>'form-control']) !!}-->
                        <!--</div>-->
                    </div>
                </div>
            </div>
            <div class="group-panel">
                <label class="group-title">Uttar Pradesh</label>
                <div class="group-content">
                    <div class="row">
                        @php
                            $mundra = null;
                            $gahndidam = null;
                            $nawaShehar = null;
                            $uttarPradeshPrice = $prices->where('state','uttar_pradesh')->whereIn('route',['mundra_port','kandla_port','gandhidham_gujarat','nhavasheva_mumbai']);
                            if(!$uttarPradeshPrice->isEmpty()){
                                $mundra = $uttarPradeshPrice->where('route','mundra_port')->first();
                                if($mundra != null){
                                    $mundra = $mundra->price;
                                }
                                $kandla_port = $uttarPradeshPrice->where('route','kandla_port')->first();
                                if($kandla_port != null){
                                    $kandla_port = $kandla_port->price;
                                }
                                $nawaShehar = $uttarPradeshPrice->where('route','nhavasheva_mumbai')->first();
                                if($nawaShehar != null){
                                    $nawaShehar = $nawaShehar->price;
                                }
                            }
                        @endphp
                        <div class="col-md-4">
                            {!! Form::label('mundra_port','Mundra Port') !!}
                            {!! Form::text('uttar_pradesh[mundra_port]',$mundra,['class'=>'form-control']) !!}
                        </div>
                        <div class="col-md-4">
                            {!! Form::label('kandla_port','Kandla Port') !!}
                            {!! Form::text('uttar_pradesh[kandla_port]',$kandla_port,['class'=>'form-control']) !!}
                        </div>
                        <!--<div class="col-md-4">-->
                        <!--    {!! Form::label('nhavasheva_mumbai','Nhava Sheva-Mumbai') !!}-->
                        <!--    {!! Form::text('gujarat[nhavasheva_mumbai]',$nawaShehar,['class'=>'form-control']) !!}-->
                        <!--</div>-->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@section('scripts')
    <script src="{{ asset('js/ports.js?ref='.rand(1111,9999)) }}"></script>
@endsection