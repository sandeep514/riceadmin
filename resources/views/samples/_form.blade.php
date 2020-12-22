<div class="box-body">
    <div class="row">
        @php
            if(isset($model)){
                $date = null;
            }else{
                $date = date('d-m-Y');
            }
        @endphp
        <div class="form-group col-md-4 @error('date') has-error @enderror">
            {!! Form::label('date','Date*') !!}
            {!! Form::text('date',$date,['class'=>'form-control datepicker','id'=>'category','readonly']) !!}
            @error('date')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>
        <div class="form-group col-md-4 @error('supplier') has-error @enderror">
            {!! Form::label('supplier','Supplier*') !!}
            {!! Form::select('supplier',\App\User::sellers(),null,['class'=>'form-control','id'=>'category','placeholder'=>'Select Seller']) !!}
            @error('supplier')
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
        <div class="form-group col-md-4 @error('packing_type') has-error @enderror">
            {!! Form::label('packing_type','Packing Type*') !!}
            {!! Form::select('packing_type',\App\PackingType::packingTypes(),null,['class'=>'form-control','id'=>'category','placeholder'=>'Select Packing Type']) !!}
            @error('packing_type')
            <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>
        <div class="form-group col-md-4 @error('packing') has-error @enderror">
            @php
                $selectedPacking = '';
                if(isset($model)){
                    $selectedPacking = $model->packing;
                }
            @endphp
            {!! Form::label('packing','Packing*') !!}
            <select name="packing" class="form-control packing">
                <option>Select Packing</option>
                @foreach(\App\Packing::packings() as $ket => $packing)
                    <option value="{{ $packing->id }}" data-code="{{ $packing->value }}" {{ ($packing->id == $selectedPacking)?'selected':'' }}>{{ $packing->code }}</option>
                @endforeach
            </select>
            @error('packing')
            <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>
        <div class="form-group col-md-4 @error('no_of_bags') has-error @enderror">
            {!! Form::label('no_of_bags','No. of Bags*') !!}
            {!! Form::select('no_of_bags',\App\Sample::noOfBags(),null,['class'=>'form-control no_of_bags','id'=>'no_of_bags','placeholder'=>'Select No of Bags']) !!}
            @error('no_of_bags')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>
        @php
            $showBagsQty = 'none';
            if(old('no_of_bags') == 'manual'){
                $showBagsQty = 'block';
            }else{
                $showBagsQty = 'none';
            }
            if(isset($model) && $model->no_of_bags == 'manual'){
                $showBagsQty = 'block';
            }
        @endphp
        <div class="form-group col-md-4 @error('bags_qty') has-error @enderror bags_qty" style="display: {{ $showBagsQty }};">
            {!! Form::label('bags_qty','Enter Bags Qty*') !!}
            {!! Form::number('bags_qty',null,['class'=>'form-control bags_qty_input','id'=>'bags_qty','placeholder'=>'Enter bags qty']) !!}
            @error('bags_qty')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>
        <div class="form-group col-md-4 @error('qty') has-error @enderror">
            {!! Form::label('qty','Qty') !!}
            {!! Form::number('qty',null,['class'=>'form-control qty','id'=>'category','placeholder'=>'Enter Qty']) !!}
            @error('qty')
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
    <script src="{{ asset('js/sample.js?ref='.rand(1111,9999)) }}" type="text/javascript"></script>
@endsection
