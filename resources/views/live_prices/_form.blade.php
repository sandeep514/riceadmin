<style>
    .nonbasmatitabs .nav>li>a {
        padding: 10px 11px;
    }    
    .basmatitabs .nav>li>a {
        padding: 10px 11px;
    }
</style>
<div class="box-body">
    <div class="row">
        <div class="form-group col-md-12 @error('name') has-error @enderror">
            {!! Form::label('name','Name*') !!}
            {!! Form::select('name',\App\RiceName::qualityNamesForLivePrice(),request()->rice_name,['class'=>'form-control','id'=>'category','placeholder'=>'Select Name']) !!}
            
            @error('name')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
            
        </div>
    </div>
    @if($riceModel != null)
        @if($riceModel->type == 'basmati')
            <div class="responsiveTabs basmatitabs">
                <ul id="myTab" class="nav nav-tabs" style="margin-bottom: 15px;">
                    @foreach( $livePrice as $k => $v )
                        <li class="">
                            <a href="#model{{ str_replace(' ' , '_' , $k) }}" data-toggle="tab">{{ str_replace(' ','_', $k) }}</a>
                        </li>
                    @endforeach
                </ul>
                <div id="myTabContent" class="tab-content" >
                    @foreach( $livePrice as $keyy => $val )
                        <div class="tab-pane fade in " id="model{{ str_replace(' ' , '_' , $keyy) }}">
                            <div class="row">
                                <div class="col-md-12 text-center">
                                    <h3>Price Details</h3>
                                    <b>Rice Name: </b> {{ $riceModel->name }}
                                        <div class="row text-left" style="margin-top: 20px;">
                                            <div class="col-md-12 inputs">
                                                {!! Form::label('na','Click For All NA: ') !!}
                                                {!! Form::checkbox('all_na',null,null,['class'=>'check_for_na']) !!}
                                                <table class="table table-striped">
                                                    <thead>
                                                    <tr>
                                                        <th>Rice Type</th>
                                                        <th>Opening</th>
                                                        <th>Closing</th>
                                                        <th>Month Start</th>
                                                        <th>Month End</th>
                                                        <th>Crop Year</th>
                                                        <th>Crop Grade</th>
                                                        <th>Min Price</th>
                                                        <th>Max Price</th>
                                                        <th>Up/Down</th>
                                                        <th>Action</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @foreach($riceForm as $key => $form)
                                                        @php
                                                            $min = null;
                                                            $max = null;
                                                            $opening = null;
                                                            $closing = null;
                                                            $upDown = null;
                                                            $cropYear = null;
                                                            $cropGrade = null;
                                                            $monthStart = null;
                                                            $monthEnd = null;
                                                            
                                                            if($lastPrices != null){
                                                                $details = $lastPrices->where('form',$form->id)->where('state', str_replace(' ' , '_' , $keyy) )->first();
                                                                if($details != null){
                                                                    $min = $details->min_price;
                                                                    $max = $details->max_price;
                                                                    $opening = $details->opening;
                                                                    $closing = $details->closing;
                                                                    $monthStart = $details->monthStart;
                                                                    $monthEnd = $details->monthEnd;
                                                                    $max = $details->max_price;
                                                                    $upDown = $details->up_down;
                                                                    $cropYear = $details->cropYear;
                                                                    $cropGrade = $details->cropGrade;
                                                                }
                                                            }
                                                        @endphp
                                                            <tr>
                                                                <!-- {!! Form::open(['route'=>'save.price' , 'id' => 'formCreate']) !!} -->
                                                                    <td class="col-md-2">
                                                                        <input type="checkbox" name="check" class="check_user_templete">{{ $form->form_name }}
                                                                    </td>
                                                                    
                                                                    <input type="hidden" name="state" value="{{str_replace(' ' , '_' , $keyy)}}">
                                                                    <input type="hidden" name="form" value="{{$form->id}}">
                                                                    <input type="hidden" name="name" value="{{$riceModel->id}}">

                                                                    <td class="col-md-1">
                                                                        <input type="text" value="{{ $opening }}" name="opening[{{str_replace(' ' , '_' , $keyy)}}][{{$form->id}}]" placeholder="Opening" class="form-control" > 
                                                                    </td>
                                                                    <td class="col-md-1"> 
                                                                        <input type="text" value="{{ $closing }}" name="closing[{{str_replace(' ' , '_' , $keyy)}}][{{$form->id}}]" placeholder="Closing" class="form-control" > 
                                                                    </td>

                                                                    <td class="col-md-1">
                                                                        <input type="number" min="1" max="12" value="{{ $monthStart }}" name="monthStart[{{str_replace(' ' , '_' , $keyy)}}][{{$form->id}}]" placeholder="Month Start" class="form-control" > 
                                                                    </td>
                                                                    <td class="col-md-1"> 
                                                                        <input type="number" min="1" max="12" value="{{ $monthEnd }}" name="monthEnd[{{str_replace(' ' , '_' , $keyy)}}][{{$form->id}}]" placeholder="Month End" class="form-control" > 
                                                                    </td>


                                                                    <td class="col-md-1">
                                                                        <select name="cropYear[{{str_replace(' ' , '_' , $keyy)}}][{{$form->id}}]" class='form-control'>
                                                                            @foreach($lastYears as $k => $v)
                                                                                <option value="{{ $v }}" {{ ($cropYear == $v)?'selected':'' }}>{{$v}}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </td>
                                                                    <td class="col-md-1">
                                                                        <select name="cropGrade[{{str_replace(' ' , '_' , $keyy)}}][{{$form->id}}]" class='form-control'>
                                                                            @foreach(App\RiceForm::$grade as $k => $v)
                                                                                <option value="{{ $k }}" {{ ($cropGrade == $v)?'selected':'' }}>{{$v}}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </td>
                                                                    <td class="col-md-1">
                                                                        {!! Form::text('min['.str_replace(' ' , '_' , $keyy).']['.$form->id.']',$min,['class'=>'form-control']) !!}
                                                                    </td>
                                                                    <td class="col-md-1">
                                                                        {!! Form::text('max['.str_replace(' ' , '_' , $keyy).']['.$form->id.']',$max,['class'=>'form-control']) !!}
                                                                    </td>
                                                                    <td class="col-md-1">
                                                                        {!! Form::select('up_down['.str_replace(' ' , '_' , $keyy).']['.$form->id.']',['up'=>'Up','down'=>'Down','stable'=>'Stable'],$upDown,['class'=>'form-control']) !!}
                                                                    </td>
                                                                    <td class="col-md-1">
                                                                        <input type="submit" name="submitPrice" id="submitPrice" value="Submit">
                                                                    </td>
                                                                <!-- {!! Form::close() !!} -->
                                                            </tr>


                                                    @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="responsiveTabs nonbasmatitabs">
                <ul id="myTab" class="nav nav-tabs" style="margin-bottom: 15px;">
                    @foreach( $livePrice as $k => $v )
                        
                        <li class="">
                            <a href="#model{{ str_replace(' ' , '_' , $k) }}" data-toggle="tab">{{ str_replace(' ' , '_' , $k) }}</a>
                        </li>
                    @endforeach
                </ul>
                <div id="myTabContent" class="tab-content " >
                    @foreach( $livePrice as $keyy => $val )

                        <div class="tab-pane fade in" id="model{{ str_replace(' ' , '_' , $keyy) }}">
                            <div class="row">
                                <div class="col-md-12 text-center">
                                    <h3>Price Details</h3>
                                    <b>Rice Name: </b> {{ $riceModel->name }}
                                    <div class="row text-left" style="margin-top: 20px;">
                                        <div class="col-md-12 inputs">
                                    {{-- {!! Form::label('na','Click For All NA: ') !!}
                                            {!! Form::checkbox('all_na',null,null,['class'=>'check_for_na']) !!} --}}
                                            <table class="table table-striped">
                                                <thead>
                                                <tr>
                                                    <th>Rice Type</th>
                                                    <th>Opening</th>
                                                    <th>Closing</th>
                                                    <th>Month Start</th>
                                                    <th>Month End</th>
                                                    <th>Crop Year</th>
                                                    <th>Crop Grade</th>
                                                    <th>Min Price</th>
                                                    <th>Max Price</th>
                                                    <th>Up/Down</th>
                                                </tr>
                                                </thead>
                                                <tbody>


                                                @foreach($riceForm as $key => $form)
                                                    @php
                                                        $min = null;
                                                        $max = null;
                                                        $opening = null;
                                                        $closing = null;
                                                        $monthStart = null;
                                                        $monthEnd = null;
                                                        $upDown = null;
                                                        $cropYear = null;
                                                        $cropGrade = null;

                                                        if($lastPrices != null){
                                                            $details = $lastPrices->where('form',$form->id)->where('state', str_replace(' ' , '_' , $keyy))->first();
                                                            if($details != null){
                                                                $min = $details->min_price;
                                                                $max = $details->max_price;
                                                                $opening = $details->opening;
                                                                $closing = $details->closing;
                                                                $monthStart = $details->monthStart;
                                                                $monthEnd = $details->monthEnd;
                                                                $upDown = $details->up_down;
                                                                $cropYear = $details->cropYear;
                                                                $cropGrade = $details->cropGrade;
                                                            }
                                                        }
                                                    @endphp
                                                    
                                                        <tr>
                                                            <td> <input type="checkbox" name="check" class="check_user_templete"> {{ $form->form_name }}</td>
                                                            <input type="hidden" name="state" value="{{str_replace(' ' , '_' , $keyy)}}">
                                                            <input type="hidden" name="form" value="{{$form->id}}">
                                                            <input type="hidden" name="name" value="{{$riceModel->id}}">
                                                            <td class="col-md-1">
                                                                <input type="text" value="{{ $opening }}" name="opening[{{str_replace(' ' , '_' , $keyy)}}][{{$form->id}}]" placeholder="Opening" class="form-control" > 
                                                            </td>
                                                            <td class="col-md-1"> 
                                                                <input type="text" value="{{ $closing }}" name="closing[{{str_replace(' ' , '_' , $keyy)}}][{{$form->id}}]" placeholder="Closing" class="form-control" > 
                                                            </td>
                                                            <td class="col-md-1">
                                                                <input type="number" min="1" max="12" value="{{ $monthStart }}" name="monthStart[{{str_replace(' ' , '_' , $keyy)}}][{{$form->id}}]" placeholder="Month Start" class="form-control" > 
                                                            </td>
                                                            <td class="col-md-1"> 
                                                                <input type="number" min="1" max="12" value="{{ $monthEnd }}" name="monthEnd[{{str_replace(' ' , '_' , $keyy)}}][{{$form->id}}]" placeholder="Month End" class="form-control" > 
                                                            </td>
                                                            <td>
                                                                <select name="cropYear[{{str_replace(' ' , '_' , $keyy)}}][{{$form->id}}]" class='form-control'>
                                                                    @foreach($lastYears as $k => $v)
                                                                        <option value="{{ $v }}" {{ ($cropYear == $v)?'selected':'' }}>{{$v}}</option>
                                                                    @endforeach
                                                                </select>

                                                            </td>
                                                            <td>
                                                                
                                                                <select name="cropGrade[{{str_replace(' ' , '_' , $keyy)}}][{{$form->id}}]" class='form-control'>
                                                                    @foreach(App\RiceForm::$grade as $k => $v)
                                                                        <option value="{{ $k }}" {{ ($cropGrade == $v)?'selected':'' }}>{{$v}}</option>
                                                                    @endforeach
                                                                </select>

                                                            </td>
                                                            <td>
                                                                {!! Form::text('min['.str_replace(' ' , '_' , $keyy).']['.$form->id.']',$min,['class'=>'form-control']) !!}
                                                            </td>
                                                            <td>
                                                                {!! Form::text('max['.str_replace(' ' , '_' , $keyy).']['.$form->id.']',$max,['class'=>'form-control']) !!}
                                                            </td>
                                                            <td>
                                                                {!! Form::select('up_down['.str_replace(' ' , '_' , $keyy).']['.$form->id.']',['up'=>'Up','down'=>'Down','stable'=>'Stable'],$upDown,['class'=>'form-control']) !!}
                                                            </td>

                                                            <td class="col-md-1">
                                                                <input type="submit" name="submitPrice" id="submitPrice" value="Submit">
                                                            </td>
                                                        </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endif
</div>


@section('scripts')
    <script src="{{ asset('js/live-price.js?ref='.rand(1111,9999)) }}"></script>
    <script>
        $(document).ready(function(){
            $('#myTab li:first-child a').click();

            $(document).on('click', '#submitPrice', function (e) {
                e.preventDefault();

                let $row = $(this).closest('tr'); // current row
                let formData = new FormData();

                // checkbox
                let checked = $row.find('.check_user_templete').is(':checked');
                formData.append('checked', checked ? 1 : 0);
                formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

                // all inputs & selects in this row
                $row.find('input, select').each(function () {
                    let name = $(this).attr('name');
                    if (!name) return;

                    if ($(this).attr('type') === 'checkbox') {
                        formData.append(name, $(this).is(':checked') ? 1 : 0);
                    } else {
                        formData.append(name, $(this).val());
                    }
                });

                // Debug
                // for (let pair of formData.entries()) {
                //     console.log(pair[0] + ' => ' + pair[1]);
                // }
                

                $.ajax({
                    url: '{{ url("administrator/live/prices/save/for/single") }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (res) {
                        alert("price updated");
                    },error: function(err){
                        alert('something went wrong')
                    }
                });
            });

        });
    </script>
@endsection