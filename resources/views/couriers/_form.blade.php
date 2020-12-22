<div class="box-body">
    <div class="row">
        @php
            $today = \Carbon\Carbon::now()->format('d-m-Y');
        @endphp
        <div class="form-group col-md-6 @error('date') has-error @enderror">
            {!! Form::label('date','Date*') !!}
            {!! Form::text('date',$today,['class'=>'form-control datepicker','id'=>'category','readonly']) !!}
            @error('date')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>

        <div class="form-group col-md-6 @error('sent_via') has-error @enderror">
            {!! Form::label('sent_via','Sent Via*') !!}
            {!! Form::select('sent_via',\App\Courier::$sentVia,null,['class'=>'form-control','id'=>'category','placeholder'=>'Select Sent Via']) !!}
            @error('sent_via')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>
        <div class="form-group col-md-6 @error('details') has-error @enderror">
            {!! Form::label('details','Details/AWB no/Person name*') !!}
            {!! Form::textarea('details',null,['class'=>'form-control','id'=>'category']) !!}
            @error('details')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>
    </div>
    @php
        $samples = App\Sample::whereCourierStatus(0)->get();
    @endphp
    <div class="row">
        <div class="col-md-12">
            <h3>Select Samples
                <small>
                    @error('samples')
                        <span class="help-block text-danger" role="alert" style="color: red;">
                            {{ $message }}
                        </span>
                    @enderror
                </small>
            </h3>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Supplier</th>
                        <th>Quality</th>
                        <th>Packing</th>
                        <th>Packing Type</th>
                        <th>Qty</th>
                        <th>Photo</th>
                        <th>
                            <div class="checkbox icheck">
                                <label>
                                    <input type="checkbox" class="select-all"> Select All
                                </label>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody>

                    @if(isset($model))
                        @php
                            $selectedSamples = json_decode($model->samples,true);
                            $selectedSamples = \App\Sample::whereIn('id',array_keys($selectedSamples))->get();
                        @endphp
                        @foreach($selectedSamples as $key => $sample)
                            <tr>
                                <td>{{ $sample->date }}</td>
                                <td>{{ $sample->supplier_rel->name }}</td>
                                <td>{{ $sample->quality_rel->name }}</td>
                                <td>{{ $sample->packing_rel->value }}</td>
                                <td>{{ $sample->packing_type_rel->name }}</td>
                                <td>{{ $sample->qty }}</td>
                                <td>
                                    <img src="{{ asset('sample-images/'.$sample->photo) }}" width="100"/>
                                </td>
                                <td>
                                    <div class="checkbox icheck">
                                        <label>
                                            <input type="checkbox" name="sample[{{ $sample->id }}]" checked class="sample-selection">
                                        </label>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @endif

                    @foreach($samples as $key => $sample)
                        <tr>
                            <td>{{ $sample->date }}</td>
                            <td>{{ $sample->supplier_rel->name }}</td>
                            <td>{{ $sample->quality_rel->name }}</td>
                            <td>{{ $sample->packing_rel->value }}</td>
                            <td>{{ $sample->packing_type_rel->name }}</td>
                            <td>{{ $sample->qty }}</td>
                            <td>
                                @if($sample->photo != '')
                                    <img src="{{ asset('sample-images/'.$sample->photo) }}" width="100"/>
                                @else
                                    No Sample Image
                                @endif
                            </td>
                            <td>
                                <div class="checkbox icheck">
                                    <label>
                                        <input type="checkbox" name="sample[{{ $sample->id }}]" class="sample-selection">
                                    </label>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@section('scripts')
    <script type="text/javascript">
        $(document).ready(function(){
            $('.select-all').on('ifChanged', function(){
                if($(this).is(':checked')){
                    $('.sample-selection').each(function(){
                        $(this).iCheck('check')
                    });
                }else{
                    $('.sample-selection').each(function(){
                        $(this).iCheck('uncheck')
                    });
                }
            });
        });
    </script>
@endsection
