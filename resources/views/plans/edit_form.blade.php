<style>
    .lineheight25{
        line-height: 2.5;
    }
</style>
<div class="box-body">
    <div class="row margin-top-10">
        <div class="col-md-12">
            <div class="group-panel">
                <label class="group-title">Edit Plan</label>
                <div class="group-content">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('plan_name','Plan Name') !!}
                                {!! Form::text('plan_name', $plan[0]->plan_name ,['class'=>'form-control', 'required' => 'required']) !!}
                                <input type="hidden" name="plan_id" value="{{ $plan[0]->id }}">
                            </div>
                            <div class="col-md-12">
                                <div class="row">
                                    <h3 style="font-size: 18px;margin-left: 13px;font-weight: 600;">Chart Intervals</h3>
                                    @php
                                        $chartInt_array = $chartIntArray;
                                    @endphp
                                    
                                    @foreach( $ChartInterval as $k => $v )
                                        @if($k > 0)
                                        <div class="col-md-2">
                                            {!! Form::checkbox('chartint['.$v->id.']', $v->id , (in_array($v->id , $chartInt_array) ? true : false ) ,['class'=>'form-control']) !!}
                                            {!! Form::label( 'chartint',$v->name ) !!}
                                        </div>
                                        @endif
                                    @endforeach   
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    
                    <div class="box-body">
                        <div class="row margin-top-10">
                            <div class="col-md-12">
                                <div class="group-panel">
                                    <label class="group-title">Plan</label>
                                    <div class="group-content">
                                     
                                        <div class="row">
                                            <!--<h3 style="font-size: 18px;margin-left: 13px;font-weight: 600;">Sub Plan</h3>-->
                                            @php
                                                $sub_plan = [];
                                            @endphp

                                          

                                            @foreach( $SubPlan as $k => $v )
                                                @php
                                                    $actualPrice = '';
                                                    $offerPrice = '';
                                                    
                                                    $data = $v['price'];
                                                    if( array_key_exists('actualPrice' , $data) ){
                                                        $actualPrice = $data['actualPrice'];
                                                    }
                                                    if( array_key_exists('offerPrice' , $data) ){
                                                        $offerPrice = $data['offerPrice'];
                                                    }
                                                @endphp
                                                
                                                <div class="col-md-4">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            {!! Form::label('subplan[]',$v['data']['name']) !!}
                                                            <div class="row">
                                                                <div class="col-md-4">
                                                                    {!! Form::label('subplan['.$v['data']->id.']','Actual Price' , ['class' => 'lineheight25']) !!}
                                                                </div>
                                                                <div class="col-md-8">  
                                                                    {!! Form::text('subplan['.$v['data']->id.'][actualPrice]', $actualPrice ,['placeholder' => 'Actual Price' ,'class'=>'form-control']) !!}
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-12">
                                                            <div class="row">
                                                                <div class="col-md-4">
                                                                    {!! Form::label('subplan['.$v['data']->id.']','Offer Price' , ['class' => 'lineheight25']) !!}
                                                                </div>
                                                                <div class="col-md-8">
                                                                    {!! Form::text('subplan['.$v['data']->id.'][offerPrice]', $offerPrice ,['placeholder' => 'Offer Price' ,'class'=>'form-control']) !!}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>