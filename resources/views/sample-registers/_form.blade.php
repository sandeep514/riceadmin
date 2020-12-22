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
        @php
            if(isset($model)){
                $maxSntc = $model->sntc_no;
            }
        @endphp
        <div class="form-group col-md-4 @error('sntc_no') has-error @enderror">
            {!! Form::label('sntc_no','SNTC No*') !!}
            {!! Form::text('sntc_no',$maxSntc,['class'=>'form-control','id'=>'category','readonly'=>true]) !!}
            @error('sntc_no')
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
        <div class="form-group col-md-4 @error('packing_type') has-error @enderror">
            {!! Form::label('packing_type','Packing Type*') !!}
            {!! Form::select('packing_type',\App\PackingType::packingTypes(),null,['class'=>'form-control','placeholder'=>'Select Packing Type']) !!}
            @error('packing_type')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>
        <div class="form-group col-md-4 @error('no_of_bags') has-error @enderror">
            {!! Form::label('no_of_bags','Qty*') !!}
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
            {!! Form::label('qty_per_bag','No of Bags*') !!}
            {!! Form::text('qty_per_bag',null,['class'=>'form-control bags_qty_input','placeholder'=>'Enter Qty Per Bag']) !!}
            @error('qty_per_bag')
            <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>
        <div class="form-group col-md-4 @error('seller_qty') has-error @enderror">
            {!! Form::label('seller_qty','Seller Qty*') !!}
            {!! Form::text('seller_qty',null,['class'=>'form-control seller_qty','placeholder'=>'Enter Qty']) !!}
            @error('seller_qty')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>
        <div class="form-group col-md-4 @error('seller_offer') has-error @enderror">
            {!! Form::label('seller_offer','Seller Offer*') !!}
            {!! Form::number('seller_offer',null,['class'=>'form-control','placeholder'=>'Seller offer']) !!}
            @error('seller_offer')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>

        <div class="form-group col-md-4 @error('photo') has-error @enderror" style="margin-top: 10px">
            {!! Form::label('photo','Photo') !!}
            {!! Form::file('photo',null,['class'=>'form-control']) !!}
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
    <script type="text/javascript" src="{{ asset('js/sample-register.js?ref='.rand(1111,9999)) }}"></script>
@endsection

