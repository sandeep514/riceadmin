<div class="box-body">
    <div class="row">
        <div class="form-group col-md-4 @error('date') has-error @enderror">
            {!! Form::label('date','Date*') !!}
            {!! Form::text('date',null,['class'=>'form-control datepicker','id'=>'category','readonly']) !!}
            @error('date')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>
        <div class="form-group col-md-4 @error('sntc_no') has-error @enderror">
            {!! Form::label('sntc_no','SNTC No*') !!}
            {!! Form::select('sntc_no',\App\SampleRegister::registered_samples(),null,['class'=>'form-control','placeholder'=>'Select SNTC']) !!}
            @error('sntc_no')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>
        <div class="form-group col-md-4 @error('buyer') has-error @enderror">
            {!! Form::label('buyer','Buyer*') !!}
            {!! Form::select('buyer',\App\User::buyers(),null,['class'=>'form-control','id'=>'category','placeholder'=>'Select Buyer']) !!}
            @error('buyer')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>
        <div class="form-group col-md-4 @error('quality') has-error @enderror">
            {!! Form::label('quality','Quality*') !!}
            {!! Form::select('quality',\App\Quality::qualities(),null,['class'=>'form-control','id'=>'category','placeholder'=>'Select Quality']) !!}
            @error('quality')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>
        <div class="form-group col-md-4 @error('bag_type') has-error @enderror">
            {!! Form::label('bag_type','Packing Type*') !!}
            {!! Form::select('bag_type',\App\PackingType::packingTypes(),null,['class'=>'form-control','id'=>'category','placeholder'=>'Select Packing Type']) !!}
            @error('bag_type')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>
        <div class="form-group col-md-4 @error('no_of_bags') has-error @enderror">
            {!! Form::label('no_of_bags','No of Bags*') !!}
            {!! Form::select('no_of_bags',\App\Sample::noOfBags(),null,['class'=>'form-control no_of_bags','id'=>'no_of_bags','placeholder'=>'Select No of Bags']) !!}
            @error('no_of_bags')
            <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>
        @php
            $showQty = 'none';
            if(old('no_of_bags') == 'manual'){
                $showQty = 'block';
            }else{
                $showQty = 'none';
            }
            if(isset($model) && $model->no_of_bags == 'manual'){
                $showQty = 'block';
            }
        @endphp
        <div class="form-group col-md-4 @error('qty_per_bag') has-error @enderror qty_per_bag" style="display: {{ $showQty }}">
            {!! Form::label('qty_per_bag','Qty Per Bag*') !!}
            {!! Form::text('qty_per_bag',null,['class'=>'form-control','id'=>'category','placeholder'=>'Enter Qty Per Bag']) !!}
            @error('qty_per_bag')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>
        <div class="form-group col-md-4 @error('qty') has-error @enderror">
            {!! Form::label('qty','Qty') !!}
            {!! Form::number('qty',null,['class'=>'form-control','id'=>'category','placeholder'=>'Enter Qty']) !!}
            @error('qty')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>
        <div class="form-group col-md-4 @error('awb_no') has-error @enderror">
            {!! Form::label('awb_no','AWB No*') !!}
            {!! Form::text('awb_no',null,['class'=>'form-control','id'=>'category','placeholder'=>'Enter AWB No']) !!}
            @error('awb_no')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>

        <div class="form-group col-md-4 @error('photo') has-error @enderror" style="margin-top: 10px">
            {!! Form::label('photo','Photo') !!}
            {!! Form::file('photo',null,['class'=>'form-control','id'=>'category']) !!}
            @error('photo')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>
        @if(isset($model) && $model->photo != '')
            <div class="col-md-6">
                <img src="{{ asset('sample-images/'.$model->photo) }}" width="150" />
            </div>
        @endif
    </div>

</div>

@section('scripts')
    <script type="text/javascript" src="{{ asset('js/sample-outward.js') }}"></script>
@endsection
