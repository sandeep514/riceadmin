<div class="box-body">
    <div class="row">
        <div class="form-group col-md-6 @error('wand') has-error @enderror">
            <!-- {!! Form::label('wand','Wand*') !!} -->
            <!-- <select name="wandType" class="form-control">
                @foreach($riceName as $k => $v)
                    <option value="{{ $v->id }}" >{{ $v->name }}</option>
                @endforeach
            </select> -->
            @php
                $url = $_SERVER['REDIRECT_URL'];
                $explodedURL = explode('/' , $url);
                $countURL = count($explodedURL);
                $lastIndex = ($countURL-1);
                $wandTypeId = base64_decode($explodedURL[$lastIndex]);
            @endphp
            
            <input type="hidden" name="wandType" value="{{ $wandTypeId }}">
            @foreach($wandTypeModal as $k => $v)
                <div class="row">
                    <div class="col-md-6 col-sm-6 col-lg-6">
                        <p class="">{{ $v->type }}</p>    
                    </div>
                    <div class="col-md-6 col-sm-6 col-lg-6">
                        <input type="text" class=" form-control" name="wandValue[{{ $v->id }}]" value="{{ (array_key_exists($v->id , $WandModelData)) ? $WandModelData[$v->id] : ''}}"  />    
                    </div>
                </div>
            @endforeach

            @error('wand')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>
    </div>
</div>
