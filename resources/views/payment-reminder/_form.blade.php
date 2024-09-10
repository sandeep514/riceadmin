<div class="box-body">
    <div class="row">
        @php
            if(isset($model)){
                $date = null;
            }else{
                $date = \Carbon\Carbon::now()->format('d-m-Y');
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
        @php
            $sntcNo = 'block';
            if(old('is_direct_deal') == 1){
                $sntcNo = 'none';
            }
            if(isset($model) && $model->is_direct_deal == 1){
                $sntcNo = 'none';
            }
        @endphp
        <div class="form-group col-md-3 @error('buyer') has-error @enderror">
            {!! Form::label('buyer','Buyer*') !!}
            {!! Form::select('buyer',\App\User::buyers(),null,['class'=>'form-control','id'=>'buyer','placeholder'=>'Select buyer']) !!}
            @error('buyer')
            <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>

        <div class="form-group col-md-3 @error('seller') has-error @enderror">
            {!! Form::label('seller','Seller*') !!}
            {!! Form::select('seller',\App\User::sellers(),null,['class'=>'form-control','id'=>'buyer','placeholder'=>'Select Seller']) !!}
            @error('seller')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>

        <div class="form-group col-md-3 @error('invoice_number') has-error @enderror">
            {!! Form::label('invoice_number','Invoice Number*') !!}
            {!! Form::text('invoice_number',null,['class'=>'form-control','id'=>'invoice_number','placeholder'=>'Enter Invoice Number']) !!}
            @error('invoice_number')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>

        <div class="form-group col-md-3 @error('amount') has-error @enderror">
            {!! Form::label('amount','Amount*') !!}
            {!! Form::text('amount',null,['class'=>'form-control','id'=>'amount','placeholder'=>'Enter Amount']) !!}
            @error('amount')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>

        <div class="form-group col-md-3 @error('rec_amount') has-error @enderror">
            {!! Form::label('rec_amount','Received Amount*') !!}
            {!! Form::text('rec_amount',null,['class'=>'form-control','id'=>'amount','placeholder'=>'Enter Amount']) !!}
            @error('rec_amount')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>
    </div>
</div>

@section('scripts')
    <script src="{{ asset('js/deals.js?ref='.rand(1111,9999)) }}"></script>
@endsection

