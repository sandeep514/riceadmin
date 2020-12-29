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
    @foreach($prices as $k => $v)
        <div class="row margin-top-10">
            <div class="col-md-12">
                <div class="group-panel">
                    <label class="group-title">{{ $k }}</label>
                    <div class="group-content">
                        <div class="row">
                            @foreach( $v as $kk => $vv )
                                @php
                                    $route = explode('_', $vv->route);
                                    $route = implode(' ' , $route);
                                @endphp
                                <div class="col-md-4">
                                    {!! Form::label($vv->route,$route) !!}
                                    {!! Form::text(ucfirst($k).'['.$vv->route.']',$vv->price,['class'=>'form-control']) !!}
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
@section('scripts')
    <script src="{{ asset('js/ports.js?ref='.rand(1111,9999)) }}"></script>
@endsection