<div class="box-body sample-report" style="display: none;">
    <div class="row">
        <div class="col-md-12">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Length</th>
                    <th>Ad Mixture</th>
                    <th>Moisture</th>
                    <th>Kett</th>
                    <th>Broken</th>
                    <th>DD</th>
                    <th>Chalky</th>
                </tr>
                </thead>
                <tbody class="sample-report-data">
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="box-body">
    <div class="row">
        <div class="form-group col-md-3 @error('sntc_no') has-error @enderror">
            {!! Form::label('sntc_no','SNTC No*') !!}
            {!! Form::select('sntc_no',\App\SampleRegister::registered_samples(),null,['class'=>'form-control','id'=>'sntc_no','placeholder'=>'Select SNTC no']) !!}
            @error('sntc_no')
            <span class="help-block text-danger" role="alert">
                            {{ $message }}
                        </span>
            @enderror
        </div>
        <div class="form-group col-md-3">
            {!! Form::label('supplier','Supplier*') !!}
            {!! Form::text('supplier',null,['class'=>'form-control supplier-details','disabled'=>true]) !!}
        </div>
        <div class="form-group col-md-3">
            {!! Form::label('quality','Quality*') !!}
            {!! Form::text('quality',null,['class'=>'form-control quality-details','disabled'=>true]) !!}
        </div>
        <div class="form-group col-md-3">
            {!! Form::label('no_of_bags','No of Bags*') !!}
            {!! Form::text('no_of_bags',null,['class'=>'form-control no_of_bags','disabled'=>true]) !!}
        </div>
        <div class="form-group col-md-3">
            {!! Form::label('bag_qty','Bag Qty*') !!}
            {!! Form::text('bag_qty',null,['class'=>'form-control bag_qty','disabled'=>true]) !!}
        </div>
    </div>
    <hr style="border-color: #CCC;"/>
    <div class="row">
        <div class="col-md-4">
            <div class="row">
                <div class="col-md-12 form-group">
                    {!! Form::label('length','Length') !!}
                    {!! Form::text('length',null,['class'=>'form-control length','disabled'=>true]) !!}
                </div>
                <div class="col-md-12 form-group">
                    {!! Form::label('ad_mixture','AdMixture') !!}
                    {!! Form::text('ad_mixture',null,['class'=>'form-control ad_mixture','disabled'=>true]) !!}
                </div>
                <div class="col-md-12 form-group">
                    {!! Form::label('sub_ad_mixture','Sub-AdMixture') !!}
                    {!! Form::text('sub_ad_mixture',null,['class'=>'form-control sub_ad_mixture','disabled'=>true]) !!}
                </div>
                <div class="col-md-12 form-group">
                    {!! Form::label('moisture','Moisture') !!}
                    {!! Form::text('moisture',null,['class'=>'form-control moisture','disabled'=>true]) !!}
                </div>
                <div class="col-md-12 form-group">
                    {!! Form::label('dd','DD') !!}
                    {!! Form::text('dd',null,['class'=>'form-control dd','disabled'=>true]) !!}
                </div>
                <div class="col-md-12 form-group">
                    {!! Form::label('broken','Broken') !!}
                    {!! Form::text('broken',null,['class'=>'form-control broken','disabled'=>true]) !!}
                </div>
                <div class="col-md-12 form-group">
                    {!! Form::label('chalky','Chalky') !!}
                    {!! Form::text('chalky',null,['class'=>'form-control chalky','disabled'=>true]) !!}
                </div>
                <div class="col-md-12 form-group">
                    {!! Form::label('kett','Kett') !!}
                    {!! Form::text('kett',null,['class'=>'form-control kett','disabled'=>true]) !!}
                </div>
                <div class="col-md-12 form-group">
                    {!! Form::label('brown_layer','Brown Layer') !!}
                    {!! Form::text('brown_layer',null,['class'=>'form-control brown_layer','disabled'=>true]) !!}
                </div>
                <div class="col-md-12 form-group">
                    {!! Form::label('stone','Stone') !!}
                    {!! Form::text('stone',null,['class'=>'form-control stone','disabled'=>true]) !!}
                </div>
                <div class="col-md-12 form-group">
                    {!! Form::label('inmature','In-Mature') !!}
                    {!! Form::text('inmature',null,['class'=>'form-control inmature','disabled'=>true]) !!}
                </div>
                <div class="col-md-12 form-group">
                    {!! Form::label('broken_pin','Broken Pin') !!}
                    {!! Form::text('broken_pin',null,['class'=>'form-control broken_pin','disabled'=>true]) !!}
                </div>
                <div class="col-md-12 form-group">
                    {!! Form::label('cooking','Cooking') !!}
                    {!! Form::text('cooking',null,['class'=>'form-control cooking','disabled'=>true]) !!}
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="row">
                <div class="col-md-12">

                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="row">
                <div class="form-group col-md-12 @error('length') has-error @enderror">
                    {!! Form::label('length','Length*') !!}
                    {!! Form::text('length',null,['class'=>'form-control','id'=>'length','placeholder'=>'Enter Length']) !!}
                    @error('length')
                    <span class="help-block text-danger" role="alert">
                        {{ $message }}
                    </span>
                    @enderror
                </div>
                <div class="form-group col-md-12 @error('ad_mixture') has-error @enderror">
                    {!! Form::label('ad_mixture','AdMixture*') !!}
                    {!! Form::text('ad_mixture',null,['class'=>'form-control','id'=>'ad_mixture','placeholder'=>'Enter AdMixture']) !!}
                    @error('ad_mixture')
                        <span class="help-block text-danger" role="alert">
                            {{ $message }}
                        </span>
                    @enderror
                </div>
                <div class="sub_mix_container">
                    @if(isset($model))
                        @php
                            $sub_ad_mixture = json_decode($model->sub_ad_mixture,true);
                        @endphp
                        @foreach($sub_ad_mixture as $key => $value)
                            <div class="form-group col-md-12 sub_mix @error('sub_ad_mixture') has-error @enderror">
                                {!! Form::label('sub_ad_mixture','Sub-AdMixture') !!}
                                @if($loop->first)
                                    <a href="javascript:void(0)" class="add_sub_mixture" style="font-size: 20px; font-weight: 600;">+</a>
                                @else
                                    <a href="javascript:void(0)" class="remove-submix" style="font-size: 20px; font-weight: 600; color: red">-</a>
                                @endif
                                {!! Form::text('sub_ad_mixture[]',$value,['class'=>'form-control','id'=>'sub_ad_mixture','placeholder'=>'Enter Sub-AdMixture']) !!}
                                @error('sub_ad_mixture')
                                    <span class="help-block text-danger" role="alert">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>
                        @endforeach
                    @else
                        <div class="form-group col-md-12 sub_mix @error('sub_ad_mixture') has-error @enderror">
                            {!! Form::label('sub_ad_mixture','Sub-AdMixture') !!} <a href="javascript:void(0)" class="add_sub_mixture" style="font-size: 20px; font-weight: 600;">+</a>
                            {!! Form::text('sub_ad_mixture[]',null,['class'=>'form-control','id'=>'sub_ad_mixture','placeholder'=>'Enter Sub-AdMixture']) !!}
                            @error('sub_ad_mixture')
                            <span class="help-block text-danger" role="alert">
                            {{ $message }}
                        </span>
                            @enderror
                        </div>
                    @endif
                </div>

                <div class="form-group col-md-12 @error('moisture') has-error @enderror">
                    {!! Form::label('moisture','Moisture*') !!}
                    {!! Form::text('moisture',null,['class'=>'form-control','id'=>'moisture','placeholder'=>'Enter Moisture']) !!}
                    @error('moisture')
                        <span class="help-block text-danger" role="alert">
                            {{ $message }}
                        </span>
                    @enderror
                </div>
                <div class="form-group col-md-12 @error('dd') has-error @enderror">
                    {!! Form::label('dd','DD*') !!}
                    {!! Form::text('dd',null,['class'=>'form-control','id'=>'dd','placeholder'=>'Enter DD']) !!}
                    @error('dd')
                    <span class="help-block text-danger" role="alert">
                            {{ $message }}
                        </span>
                    @enderror
                </div>
                <div class="form-group col-md-12 @error('broken') has-error @enderror">
                    {!! Form::label('broken','Broken*') !!}
                    {!! Form::text('broken',null,['class'=>'form-control','id'=>'broken','placeholder'=>'Enter Broken']) !!}
                    @error('broken')
                        <span class="help-block text-danger" role="alert">
                            {{ $message }}
                        </span>
                    @enderror
                </div>
                <div class="form-group col-md-12 @error('broken') has-error @enderror">
                    {!! Form::label('chalky','Chalky*') !!}
                    {!! Form::text('chalky',null,['class'=>'form-control','id'=>'chalky','placeholder'=>'Enter Chalky Details']) !!}
                    @error('chalky')
                        <span class="help-block text-danger" role="alert">
                            {{ $message }}
                        </span>
                    @enderror
                </div>
                <div class="form-group col-md-12 @error('kett') has-error @enderror">
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
                <div class="form-group col-md-12 @error('brown_layer') has-error @enderror">
                    {!! Form::label('brown_layer','Brown Layer*') !!}
                    {!! Form::text('brown_layer',null,['class'=>'form-control','id'=>'brown_layer','placeholder'=>'Enter Brown Layer']) !!}
                    @error('brown_layer')
                        <span class="help-block text-danger" role="alert">
                            {{ $message }}
                        </span>
                    @enderror
                </div>
                <div class="form-group col-md-12 @error('stone') has-error @enderror">
                    {!! Form::label('stone','Stone*') !!}
                    {!! Form::text('stone',null,['class'=>'form-control','id'=>'brown_layer','placeholder'=>'Enter Stone']) !!}
                    @error('stone')
                        <span class="help-block text-danger" role="alert">
                            {{ $message }}
                        </span>
                    @enderror
                </div>
                <div class="form-group col-md-12 @error('inmature') has-error @enderror">
                    {!! Form::label('inmature','Inmature*') !!}
                    {!! Form::text('inmature',null,['class'=>'form-control','id'=>'inmature','placeholder'=>'Enter Inmature']) !!}
                    @error('inmature')
                    <span class="help-block text-danger" role="alert">
                            {{ $message }}
                        </span>
                    @enderror
                </div>
                <div class="form-group col-md-12 @error('broken_pin') has-error @enderror">
                    {!! Form::label('broken_pin','Broken Pin*') !!}
                    {!! Form::text('broken_pin',null,['class'=>'form-control','id'=>'broken_pin','placeholder'=>'Enter Broken pin']) !!}
                    @error('broken_pin')
                        <span class="help-block text-danger" role="alert">
                            {{ $message }}
                        </span>
                    @enderror
                </div>
                <div class="form-group col-md-12 @error('cooking') has-error @enderror">
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
    <script src="{{ asset('js/deal-lab-report.js?ref='.rand(1111,9999)) }}" type="text/javascript"></script>
@endsection
