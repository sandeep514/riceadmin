<div class="box-body">
    <div class="row">
        @php
            if(!isset($model)){
                $date = \Carbon\Carbon::now()->format('d-m-Y');
            }else{
                $date = \Carbon\Carbon::parse($model->date)->format('d-m-Y');
            }
        @endphp
        <div class="form-group col-md-3 @error('date') has-error @enderror">
            {!! Form::label('date','Date*') !!}
            {!! Form::text('date',$date,['class'=>'form-control datepicker','id'=>'date','readonly'=>true]) !!}
            @error('date')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>

        <div class="form-group col-md-3 sntc_no @error('sntc_no') has-error @enderror">
            {!! Form::label('sntc_no','SNTC No*') !!}
            {!! Form::select('sntc_no',\App\SampleRegister::registered_samples(),null,['class'=>'form-control select2','id'=>'sntc_no','placeholder'=>'Select SNTC']) !!}
            @error('sntc_no')
            <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>

        <div class="form-group col-md-3 @error('offer_price') has-error @enderror">
            {!! Form::label('offer_price','Offer Price*') !!}
            {!! Form::number('offer_price',null,['class'=>'form-control','id'=>'quantity','placeholder'=>'Enter Offer Price']) !!}
            @error('offer_price')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>
    </div>
</div>

@section('scripts')
    <script src="{{ asset('js/offers.js?ref='.rand(1111,9999)) }}"></script>
@endsection

