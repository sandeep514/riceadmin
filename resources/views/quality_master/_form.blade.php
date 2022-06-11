<div class="box-body">
    <div class="row">
        <div class="form-group col-md-3 @error('date') has-error @enderror">
            {!! Form::label('is_direct_deal','Is Direct Deals*') !!}
            <br/>
            {!! Form::checkbox('is_direct_deal',1,null,['class'=>'form-control is_direct_deal','id'=>'is_direct_deal']) !!}
            @error('is_direct_deal')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>
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
        <div class="form-group col-md-3 sntc_no @error('sntc_no') has-error @enderror" style="display: {{ $sntcNo }}">
            {!! Form::label('sntc_no','SNTC No*') !!}
            {!! Form::select('sntc_no',\App\SampleRegister::registered_samples(),null,['class'=>'form-control select2','id'=>'sntc_no','placeholder'=>'Select SNTC']) !!}
            @error('sntc_no')
            <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>
        <div class="form-group col-md-3 @error('contract_no') has-error @enderror">
            {!! Form::label('contract_no','Contract No*') !!}
            {!! Form::text('contract_no',null,['class'=>'form-control','id'=>'sntc_no','placeholder'=>'Enter Contract No']) !!}
            @error('contract_no')
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
        <div class="form-group col-md-3 @error('buyer') has-error @enderror">
            {!! Form::label('buyer','Buyer*') !!}
            {!! Form::select('buyer',\App\User::buyers(),null,['class'=>'form-control','id'=>'buyer','placeholder'=>'Select buyer']) !!}
            @error('buyer')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>
        <div class="form-group col-md-3 @error('quantity') has-error @enderror">
            {!! Form::label('quantity','Quantity*') !!}
            {!! Form::text('quantity',null,['class'=>'form-control','id'=>'quantity','placeholder'=>'Enter Quantity']) !!}
            @error('quantity')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>
        <div class="form-group col-md-3">
            {!! Form::label('document','Document/Contract') !!}
            {!! Form::file('document',null) !!}
        </div>
    </div>
</div>

@section('scripts')
    <script src="{{ asset('js/deals.js?ref='.rand(1111,9999)) }}"></script>
@endsection

