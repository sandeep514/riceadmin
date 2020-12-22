<div class="box-body">
    <div class="row">
        <div class="form-group col-md-6 @error('zone_area') has-error @enderror">
            {!! Form::label('zone_area','Zone Area*') !!}
            {!! Form::text('zone_area',null,['class'=>'form-control','id'=>'category']) !!}
            @error('zone_area')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>
        <div class="form-group col-md-6 @error('city') has-error @enderror">
            {!! Form::label('city','City*') !!}
            {!! Form::select('city',\App\City::cities(),null,['class'=>'form-control city','id'=>'category','placeholder'=>'Select City']) !!}
            @error('city')
            <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>
        @php
            $display = 'none';
            if($errors->has('other_city')){
                $display = 'block';
            }
        @endphp
        <div class="form-group other_city col-md-6 @error('other_city') has-error @enderror" style="display: {{ $display }};">
            {!! Form::label('other_city','City Name*') !!}
            {!! Form::text('other_city',null,['class'=>'form-control','id'=>'category','placeholder'=>'Enter city name']) !!}
            @error('other_city')
            <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>
    </div>
</div>

@section('scripts')

    <script>
        $(document).ready(function(){
            $('.city').change(function(){
                if($(this).val() == 0){
                    $('.other_city').show();
                }else{
                    $('.other_city').hide();
                }
                if($(this).val() == ''){
                    $('.other_city').hide();
                }
            });
        });
    </script>
@endsection
