<div class="box-body">
    <div class="row">
        <input type="hidden" name="id"  value="{{ $brand->id }}">
        <div class="form-group col-md-6 @error('brand_name') has-error @enderror">
            {!! Form::label('brandName','Brand Name*') !!}
            {!! Form::text('brand_name',$brand->name,['class'=>'form-control','id'=>'brandName']) !!}
            @error('brand_name')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>
        <div class="form-group col-md-6 @error('brand_logo') has-error @enderror">
            {!! Form::label('brand_logo','Brand Logo*') !!}
            {!! Form::file('brand_logo',['class'=>'form-control','id'=>'brandLogo']) !!}
            @error('brand_logo')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
            <img src="{{ asset('uploads/brandlogo/'.$brand->image) }}" style="width: 100px">
        </div>

         <div class="form-group col-md-6 @error('brand_logo') has-error @enderror">
            {!! Form::label('brand_attachment','Brand Attachments*') !!}
            {!! Form::file('brand_attachment[]',['class'=>'form-control','id'=>'brandLogo' , 'multiple'=>true]) !!}
            @error('brand_attachment')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
            @foreach($brand->getAttachments as $k => $v)
                <div style="width: 100px;border: 2px solid #ededed;margin: 10px;border-radius: 10px;float: left;">
                    <div style="text-align: right;padding: 0px 10px;">
                        <a href="{{ route('delete.banner.attachment' ,['bannerId' => $v->id]) }}">X</a>
                    </div>
                    <img src="{{ asset('uploads/brandlogo/brandAttachment/'.$v->attachment) }}" style="width: 100px;padding: 10px;">
                </div>
            @endForeach
        </div>
    </div>
</div>
