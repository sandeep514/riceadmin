<div style="width: 100%; height: 1000px; position: fixed; top: 0px; left: 0px; background: #000; opacity: 0.5; z-index: 9998;" class="backdrop loader-cooking">

</div>
<img src="{{ asset('images/Spinner-1s-200px copy.gif') }}" style="position: fixed; z-index: 9999; left: 50%;" class="image-loader loader-cooking" />
<div class="box-body">
    <div class="row">
        <div class="form-group col-md-6 @error('sntc_no') has-error @enderror">
            {!! Form::label('sntc_no','SNTC No*') !!} <img src="{{ asset('images/Spinner-1s-200px.gif') }}" class="loader" style="width: 30px; display: none;" />
            {!! Form::select('sntc_no',\App\SampleRegister::registered_samples(),null,['class'=>'form-control','id'=>'sntc_no','placeholder'=>'Select SNTC no']) !!}
            @error('sntc_no')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>
        <div class="form-group col-md-6 @error('quality') has-error @enderror">
            {!! Form::label('quality','Quality*') !!}
            {!! Form::text('qDet',null,['class'=>'form-control quality-details','disabled'=>true]) !!}
        </div>
    </div>
    <div class="row">
        <div class="form-group col-md-4 @error('supplier') has-error @enderror">
            {!! Form::label('supplier','Supplier*') !!}
            {!! Form::text('sDet',null,['class'=>'form-control supplier-details','disabled'=>true]) !!}
        </div>
        <div class="form-group col-md-4 @error('no_of_bags') has-error @enderror">
            {!! Form::label('no_of_bags','No of Bags*') !!}
            {!! Form::text('noBags',null,['class'=>'form-control no_of_bags','disabled'=>true]) !!}
        </div>
        <div class="form-group col-md-4 @error('bag_qty') has-error @enderror">
            {!! Form::label('bag_qty','Bag Qty*') !!}
            {!! Form::text('bQty',null,['class'=>'form-control bag_qty','disabled'=>true]) !!}
        </div>
    </div>
    <div class="row">
        <div class="form-group col-md-4 @error('status') has-error @enderror">
            {!! Form::label('status','Status*') !!}
            {!! Form::select('status',\App\CookingReport::$status,null,['class'=>'form-control','id'=>'status','placeholder'=>'Select Status']) !!}
            @error('remarks')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>
        <div class="form-group col-md-4 @error('remarks') has-error @enderror">
            {!! Form::label('remarks','Remarks*') !!}
            {!! Form::textarea('remarks',null,['class'=>'form-control','id'=>'remarks','placeholder'=>'Enter Remarks','rows'=>3]) !!}
            @error('remarks')
            <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>
        <div class="form-group col-md-4 @error('image') has-error @enderror">
            {!! Form::label('image','Image*') !!}
            {!! Form::file('image',null,['class'=>'form-control','id'=>'image']) !!}
            @error('image')
            <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>
    </div>
    @if(isset($model))
        {!! Form::hidden('isEdit','true') !!}
    @else
        {!! Form::hidden('isEdit','false') !!}
    @endif
</div>


@section('scripts')
    <script src="{{ asset('js/cookings.js?ref='.rand(1111,9999)) }}" type="text/javascript"></script>
@endsection
