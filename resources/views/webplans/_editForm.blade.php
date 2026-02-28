
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
    </div>
    <div class="row">
        <div class="form-group col-md-4">
            <label>Monthly Price</label>
            <input type="number" step="0.01" class="form-control" name="monthly_price" value="{{ $data->monthly_price ?? '' }}" placeholder="Enter monthly price">
        </div>
        <div class="form-group col-md-4">
            <label>Quarterly Price</label>
            <input type="number" step="0.01" class="form-control" name="quarterly_price" value="{{ $data->quarterly_price ?? '' }}" placeholder="Enter quarterly price">
        </div>
        <div class="form-group col-md-4">
            <label>Yearly Price</label>
            <input type="number" step="0.01" class="form-control" name="yearly_price" value="{{ $data->yearly_price ?? '' }}" placeholder="Enter yearly price">
        </div>
    </div>
    <div class="row">
        <div class="form-group col-md-4">
            <label>Monthly Discount %</label>
            <input type="number" step="0.01" min="0" max="100" class="form-control" name="monthly_discount_percentage" value="{{ $data->monthly_discount_percentage ?? '' }}" placeholder="Enter monthly discount">
        </div>
        <div class="form-group col-md-4">
            <label>Quarterly Discount %</label>
            <input type="number" step="0.01" min="0" max="100" class="form-control" name="quarterly_discount_percentage" value="{{ $data->quarterly_discount_percentage ?? '' }}" placeholder="Enter quarterly discount">
        </div>
        <div class="form-group col-md-4">
            <label>Yearly Discount %</label>
            <input type="number" step="0.01" min="0" max="100" class="form-control" name="yearly_discount_percentage" value="{{ $data->yearly_discount_percentage ?? '' }}" placeholder="Enter yearly discount">
        </div>
    </div>
    <div class="row">
        <div class="form-group col-md-12">
            <div class="row">
                <div class="form-group col-md-4">
                    <label>Final Monthly</label>
                    <input type="number" step="0.01" class="form-control" name="monthly_final_amount" value="{{ $data->monthly_final_amount ?? '' }}" readonly>
                </div>
                <div class="form-group col-md-4">
                    <label>Final Quarterly</label>
                    <input type="number" step="0.01" class="form-control" name="quarterly_final_amount" value="{{ $data->quarterly_final_amount ?? '' }}" readonly>
                </div>
                <div class="form-group col-md-4">
                    <label>Final Yearly</label>
                    <input type="number" step="0.01" class="form-control" name="yearly_final_amount" value="{{ $data->yearly_final_amount ?? '' }}" readonly>
                </div>
            </div>
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
<script>
function f(a,d){a=parseFloat(a||0);d=parseFloat(d||0);return a?(a-(a*d/100)).toFixed(2):''}
function u(){var m=document.querySelector('[name="monthly_price"]')?.value;var q=document.querySelector('[name="quarterly_price"]')?.value;var y=document.querySelector('[name="yearly_price"]')?.value;var dm=document.querySelector('[name="monthly_discount_percentage"]')?.value;var dq=document.querySelector('[name="quarterly_discount_percentage"]')?.value;var dy=document.querySelector('[name="yearly_discount_percentage"]')?.value;var mf=document.querySelector('[name="monthly_final_amount"]');var qf=document.querySelector('[name="quarterly_final_amount"]');var yf=document.querySelector('[name="yearly_final_amount"]');if(mf)mf.value=f(m,dm);if(qf)qf.value=f(q,dq);if(yf)yf.value=f(y,dy)}
['monthly_price','quarterly_price','yearly_price','monthly_discount_percentage','quarterly_discount_percentage','yearly_discount_percentage'].forEach(function(n){var el=document.querySelector('[name="'+n+'"]');if(el){el.addEventListener('input',u);}});
window.addEventListener('load',u);
</script>
