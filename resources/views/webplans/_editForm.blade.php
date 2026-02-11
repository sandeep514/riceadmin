
{{-- 'data','selectedMapKeys','WebPlanKeysModel' --}}

@php
    $selectedKeys = (array_keys($selectedMapKeys->toArray()));
@endphp

<div class="box-body">
    <div class="row">
        <div class="form-group col-md-6">
            <label>Plan Key</label>
            <input type="hidden" name="id" value="{{ $data->id }}">
            <input type="text" class="form-control" name="planKey" value="{{ $data->title }}">
        </div>
        <div class="form-group col-md-6">
            <label>Amount</label>
            <input type="number" step="0.01" class="form-control" name="amount" value="{{ $data->amount ?? '' }}" placeholder="Enter amount">
        </div>
    </div>
    <div class="row">
        <div class="form-group col-md-6">
            <label>Discount %</label>
            <input type="number" step="0.01" min="0" max="100" class="form-control" name="discount_percentage" value="{{ $data->discount_percentage ?? '' }}" placeholder="Enter discount percentage">
        </div>
        <div class="form-group col-md-6">
            <label>Keys</label>
            <ul style="list-style: none;">
                @foreach($WebPlanKeysModel as $k => $v)
                    <li class="row">
                        <div class="col-md-10">
                            <p style="text-transform: capitalize;"><strong>{{ $k+1 }}.) </strong> {{  $v->key  }} {{ $v->id }}</p>    
                        </div>
                        <div class="col-md-2">
                            <input type="checkbox" name="available[]" {{ (in_array($v->id , $selectedKeys)) ? 'checked' : '' }} value="{{$v->id }}"  >
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>