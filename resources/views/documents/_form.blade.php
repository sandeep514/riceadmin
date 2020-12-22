<div class="box-body">
    <div class="row">
        @php
            if(!isset($model)){
                $date = \Carbon\Carbon::now()->format('d-m-Y');
            }else{
                $date = \Carbon\Carbon::parse($model->date)->format('d-m-Y');
            }
        @endphp
        <div class="form-group col-md-3 @error('date') has-error @enderror">
            {!! Form::label('date','Date*') !!}
            {!! Form::text('date',$date,['class'=>'form-control datepicker','id'=>'date','readonly'=>true]) !!}
            @error('date')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>

        <div class="form-group col-md-3 sntc_no @error('contract_no') has-error @enderror">
            {!! Form::label('contract_no','Contract No*') !!}
            {!! Form::select('contract_no',\App\Deal::contracts(),null,['class'=>'form-control select2','id'=>'contract_no','placeholder'=>'Select Contract']) !!}
            @error('contract_no')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>

        <div class="form-group col-md-3 @error('truck_no') has-error @enderror">
            {!! Form::label('truck_no','Truck No*') !!}
            {!! Form::text('truck_no',null,['class'=>'form-control','id'=>'quantity','placeholder'=>'Enter Truck No']) !!}
            @error('truck_no')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>

        <div class="form-group col-md-3 @error('driver_no') has-error @enderror">
            {!! Form::label('driver_no','Driver No*') !!}
            {!! Form::text('driver_no',null,['class'=>'form-control','id'=>'quantity','placeholder'=>'Enter Driver No']) !!}
            @error('driver_no')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>
        <div class="form-group col-md-3 @error('contract_copy') has-error @enderror">
            {!! Form::label('contract_copy','Contract Copy*') !!}
            {!! Form::file('contract_copy',null,['class'=>'form-control','id'=>'contract_copy']) !!}
            @error('contract_copy')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>

        <div class="form-group col-md-3 @error('bill_copy') has-error @enderror">
            {!! Form::label('bill_copy','Bill Copy*') !!}
            {!! Form::file('bill_copy',null,['class'=>'form-control','id'=>'bill_copy']) !!}
            @error('bill_copy')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>

        <div class="form-group col-md-3 @error('bilty_copy') has-error @enderror">
            {!! Form::label('bilty_copy','Bilty Copy*') !!}
            {!! Form::file('bilty_copy',null,['class'=>'form-control','id'=>'bilty_copy']) !!}
            @error('bill_copy')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>

        <div class="form-group col-md-3 @error('kanta_parchi') has-error @enderror">
            {!! Form::label('kanta_parchi','Kanta Copy*') !!}
            {!! Form::file('kanta_parchi',null,['class'=>'form-control','id'=>'kanta_parchi']) !!}
            @error('kanta_parchi')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>

        <div class="form-group col-md-3 @error('due_days') has-error @enderror">
            {!! Form::label('due_days','Due Days*') !!}
            {!! Form::text('due_days',null,['class'=>'form-control','id'=>'due_days','placeholder'=>'Due Days']) !!}
            @error('due_days')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>

        <div class="form-group col-md-3 @error('due_date') has-error @enderror">
            {!! Form::label('due_date','Due Days*') !!}
            {!! Form::text('due_date',null,['class'=>'form-control datepicker','id'=>'due_date','placeholder'=>'Due Date']) !!}
            @error('due_date')
                <span class="help-block text-danger" role="alert">
                    {{ $message }}
                </span>
            @enderror
        </div>
    </div>
    @if(isset($model))
        <div class="row">
            <div class="col-md-12">
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th>Document Name</th>
                        <th>Document</th>
                    </tr>
                    </thead>
                    <tbody>
                        @php
                            $documents = $model->only(['contract_copy','bill_copy','bilty_copy','kanta_parchi']);
                        @endphp
                        @foreach($documents as $k => $file)
                            <tr>
                                <td>{{ ucwords(str_replace('_',' ',$k)) }}</td>
                                <td>
                                    <img src="{{ asset('documents/'.$model->contract_no.'/'.$file) }}" width="100" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

@section('scripts')
    <script src="{{ asset('js/offers.js?ref='.rand(1111,9999)) }}"></script>
@endsection

