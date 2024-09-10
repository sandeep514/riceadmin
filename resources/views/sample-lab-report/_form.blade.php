<div class="box-body">
    <div class="row">
        <div class="col-md-4">
            <div class="row">
                <div class="form-group col-md-12 @error('sntc_no') has-error @enderror">
                    {!! Form::label('sntc_no','SNTC No*') !!}
                    {!! Form::select('sntc_no',\App\SampleRegister::registered_samples(),null,['class'=>'form-control','id'=>'sntc_no','placeholder'=>'Select SNTC no']) !!}
                    @error('sntc_no')
                        <span class="help-block text-danger" role="alert">
                            {{ $message }}
                        </span>
                    @enderror
                </div>
                <div class="form-inline">
                    <div class="form-group col-md-12 @error('supplier') has-error @enderror" style="margin-top: 20px;">
                        {!! Form::label('supplier','Supplier*') !!}
                        <label class="font-light margin supplier-details" style="font-weight: 400"></label>
                    </div>
                    <div class="form-group form-inline col-md-12 @error('quality') has-error @enderror" style="margin-top: 20px;">
                        {!! Form::label('quality','Quality*') !!}
                        <label class="font-light margin quality-details" style="font-weight: 400"></label>
                    </div>
                    <div class="form-group form-inline col-md-12 @error('no_of_bags') has-error @enderror" style="margin-top: 20px;">
                        {!! Form::label('no_of_bags','No of Bags*') !!}
                        <label class="font-light margin no_of_bags" style="font-weight: 400"></label>
                    </div>
                    <div class="form-group form-inline col-md-12 @error('bag_qty') has-error @enderror" style="margin-top: 20px;">
                        {!! Form::label('bag_qty','Bag Qty*') !!}
                        <label class="font-light margin bag_qty" style="font-weight: 400"></label>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="row">
                <div class="form-group col-md-6 @error('length') has-error @enderror">
                    {!! Form::label('length','Length*') !!}
                    {!! Form::text('length',null,['class'=>'form-control','id'=>'length','placeholder'=>'Enter Length']) !!}
                    @error('length')
                    <span class="help-block text-danger" role="alert">
                        {{ $message }}
                    </span>
                    @enderror
                </div>
                <div class="col-md-6">
                    <div class="row">
                        <div class="form-group col-md-6 @error('ad_mixture') has-error @enderror">
                            {!! Form::label('ad_mixture','AdMixture*') !!}
                            {!! Form::text('ad_mixture',null,['class'=>'form-control','id'=>'ad_mixture','placeholder'=>'Enter AdMixture']) !!}
                            @error('ad_mixture')
                            <span class="help-block text-danger" role="alert">
                            {{ $message }}
                        </span>
                            @enderror
                        </div>
                        <div class="form-group col-md-6 @error('sub_ad_mixture') has-error @enderror">
                            {!! Form::label('sub_ad_mixture','Sub-AdMixture') !!}
                            {!! Form::text('sub_ad_mixture',null,['class'=>'form-control','id'=>'sub_ad_mixture','placeholder'=>'Enter Sub-AdMixture']) !!}
                            @error('sub_ad_mixture')
                            <span class="help-block text-danger" role="alert">
                            {{ $message }}
                        </span>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="form-group col-md-6 @error('moisture') has-error @enderror">
                    {!! Form::label('moisture','Moisture*') !!}
                    {!! Form::text('moisture',null,['class'=>'form-control','id'=>'moisture','placeholder'=>'Enter Moisture']) !!}
                    @error('moisture')
                        <span class="help-block text-danger" role="alert">
                            {{ $message }}
                        </span>
                    @enderror
                </div>
                <div class="form-group col-md-6 @error('dd') has-error @enderror">
                    {!! Form::label('dd','DD*') !!}
                    {!! Form::text('dd',null,['class'=>'form-control','id'=>'dd','placeholder'=>'Enter DD']) !!}
                    @error('dd')
                    <span class="help-block text-danger" role="alert">
                            {{ $message }}
                        </span>
                    @enderror
                </div>
                <div class="form-group col-md-6 @error('broken') has-error @enderror">
                    {!! Form::label('broken','Broken*') !!}
                    {!! Form::text('broken',null,['class'=>'form-control','id'=>'broken','placeholder'=>'Enter Broken']) !!}
                    @error('broken')
                        <span class="help-block text-danger" role="alert">
                            {{ $message }}
                        </span>
                    @enderror
                </div>
                <div class="form-group col-md-6 @error('broken') has-error @enderror">
                    {!! Form::label('chalky','Chalky*') !!}
                    {!! Form::text('chalky',null,['class'=>'form-control','id'=>'chalky','placeholder'=>'Enter Chalky Details']) !!}
                    @error('chalky')
                        <span class="help-block text-danger" role="alert">
                            {{ $message }}
                        </span>
                    @enderror
                </div>
                <div class="form-group col-md-6 @error('kett') has-error @enderror">
                    {!! Form::label('kett','Kett*') !!}
                    {!! Form::text('kett',null,['class'=>'form-control','id'=>'kett','placeholder'=>'Enter Kett']) !!}
{{--                    <label class="pull-right margin">--}}
{{--                        {!! Form::checkbox('na',null,null,['class'=>'pull-right set_kett_null']) !!} Check For KETT N/A--}}
{{--                    </label>--}}
                    @error('kett')
                        <span class="help-block text-danger" role="alert">
                            {{ $message }}
                        </span>
                    @enderror
                </div>
                <div class="form-group col-md-6 @error('brown_layer') has-error @enderror">
                    {!! Form::label('brown_layer','Brown Layer*') !!}
                    {!! Form::text('brown_layer',null,['class'=>'form-control','id'=>'brown_layer','placeholder'=>'Enter Brown Layer']) !!}
                    @error('brown_layer')
                        <span class="help-block text-danger" role="alert">
                            {{ $message }}
                        </span>
                    @enderror
                </div>
                <div class="form-group col-md-6 @error('stone') has-error @enderror">
                    {!! Form::label('stone','Stone*') !!}
                    {!! Form::text('stone',null,['class'=>'form-control','id'=>'brown_layer','placeholder'=>'Enter Stone']) !!}
                    @error('stone')
                        <span class="help-block text-danger" role="alert">
                            {{ $message }}
                        </span>
                    @enderror
                </div>
                <div class="form-group col-md-6 @error('inmature') has-error @enderror">
                    {!! Form::label('inmature','Inmature*') !!}
                    {!! Form::text('inmature',null,['class'=>'form-control','id'=>'inmature','placeholder'=>'Enter Inmature']) !!}
                    @error('inmature')
                    <span class="help-block text-danger" role="alert">
                            {{ $message }}
                        </span>
                    @enderror
                </div>
                <div class="form-group col-md-6 @error('broken_pin') has-error @enderror">
                    {!! Form::label('broken_pin','Broken Pin*') !!}
                    {!! Form::text('broken_pin',null,['class'=>'form-control','id'=>'broken_pin','placeholder'=>'Enter Broken pin']) !!}
                    @error('broken_pin')
                        <span class="help-block text-danger" role="alert">
                            {{ $message }}
                        </span>
                    @enderror
                </div>
                <div class="form-group col-md-6 @error('cooking') has-error @enderror">
                    {!! Form::label('cooking','Cooking*') !!}
                    {!! Form::text('cooking',null,['class'=>'form-control','id'=>'cooking','placeholder'=>'Enter Cooking']) !!}
                    @error('cooking')
                    <span class="help-block text-danger" role="alert">
                            {{ $message }}
                        </span>
                    @enderror
                </div>
            </div>
        </div>
    </div>
</div>

@if(isset($model))
    {!! Form::hidden('isEdit','true') !!}
@else
    {!! Form::hidden('isEdit','false') !!}
@endif
@section('scripts')
    <script src="{{ asset('js/sample-lab-report.js?ref='.rand(1111,9999)) }}" type="text/javascript"></script>
@endsection
