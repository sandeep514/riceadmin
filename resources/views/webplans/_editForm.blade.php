
{{-- 'data','selectedMapKeys','WebPlanKeysModel' --}}

@php
    $selectedKeys = (array_keys($selectedMapKeys->toArray()));
@endphp

<div class="box-body">
    <div class="row">
        <div class="form-group col-md-6">
            <label>Role</label>
            <select name="role_id" id="role_id" class="form-control" required>
                <option value="">Select Role</option>
                @foreach($roles as $id => $name)
                    <option value="{{ $id }}" {{ ($data->role_id == $id) ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group col-md-6">
            <label>Role Category</label>
            <select name="category_id" id="category_id" class="form-control" required>
                <option value="">Select Role first</option>
            </select>
            <small class="help-block">Select a role first to load categories</small>
        </div>
    </div>
    <div class="row">
        <div class="form-group col-md-6">
            <label>Plan <small class="text-muted">(auto-generated)</small></label>
            <input type="hidden" name="id" value="{{ $data->id }}">
            <input type="text" class="form-control" name="planKey" id="plan_title" value="{{ $data->title }}" readonly placeholder="Auto-generated from Role + Category">
        </div>
    </div>
    <div class="row">
        <div class="form-group col-md-4">
            <label>Monthly Price</label>
            <input type="number" step="0.01" class="form-control" name="monthly_price" value="{{ $data->monthly_price ?? '' }}" placeholder="Enter monthly price">
        </div>
        <div class="form-group col-md-4">
            <label>Half Yearly Price</label>
            <input type="number" step="0.01" class="form-control" name="quarterly_price" value="{{ $data->quarterly_price ?? '' }}" placeholder="Enter Half Yearly price">
        </div>
        <div class="form-group col-md-4">
            <label>Yearly Price</label>
            <input type="number" step="0.01" class="form-control" name="yearly_price" value="{{ $data->yearly_price ?? '' }}" placeholder="Enter yearly price">
        </div>
    </div>
    <div class="row">
        <div class="form-group col-md-2">
            <label>Monthly Discount %</label>
            <input type="number" step="0.01" min="0" max="100" class="form-control" name="monthly_discount_percentage" value="{{ $data->monthly_discount_percentage ?? '' }}" placeholder="Enter monthly discount">
        </div>
        <div class="form-group col-md-2">
            <label>Monthly GST %</label>
            <input type="number" step="0.01" min="0" class="form-control" name="monthly_gst" value="{{ $data->monthly_gst ?? '' }}" placeholder="Enter monthly GST">
        </div>
        <div class="form-group col-md-2">
            <label>Half Yearly Discount %</label>
            <input type="number" step="0.01" min="0" max="100" class="form-control" name="quarterly_discount_percentage" value="{{ $data->quarterly_discount_percentage ?? '' }}" placeholder="Enter Half Yearly discount">
            <input type="text" class="form-control" name="quarterly_discounted_amount" value="" readonly placeholder="After discount" style="margin-top: 8px;">
        </div>
        <div class="form-group col-md-2">
            <label>Half Yearly GST %</label>
            <input type="number" step="0.01" min="0" class="form-control" name="quarterly_gst" value="{{ $data->quarterly_gst ?? '' }}" placeholder="Enter Half Yearly GST">
        </div>
        <div class="form-group col-md-2">
            <label>Yearly Discount %</label>
            <input type="number" step="0.01" min="0" max="100" class="form-control" name="yearly_discount_percentage" value="{{ $data->yearly_discount_percentage ?? '' }}" placeholder="Enter yearly discount">
        </div>
        <div class="form-group col-md-2">
            <label>Yearly GST %</label>
            <input type="number" step="0.01" min="0" class="form-control" name="yearly_gst" value="{{ $data->yearly_gst ?? '' }}" placeholder="Enter yearly GST">
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
                    <label>Final Half Yearly</label>
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
                    <li class="row" style="margin-bottom: 6px;">
                        <div class="col-md-1" style="padding-right: 0;">
                            <input type="checkbox" name="available[]" {{ (in_array($v->id , $selectedKeys)) ? 'checked' : '' }} value="{{ $v->id }}" style="margin-top: 3px;">
                        </div>
                        <div class="col-md-11" style="padding-left: 0;">
                            <p style="text-transform: capitalize; margin: 0;"><strong>{{ $k+1 }}.) </strong> {{ $v->key }}</p>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
<script>
function f(a,d,g){a=parseFloat(a||0);d=parseFloat(d||0);g=parseFloat(g||0);if(!a)return '';var after_disc=a-(a*d/100);return (after_disc+(after_disc*g/100)).toFixed(2);}
function h(a,d){a=parseFloat(a||0);d=parseFloat(d||0);if(!a)return '';return (a-(a*d/100)).toFixed(2);}
function u(){
    var m=document.querySelector('[name="monthly_price"]')?.value;
    var q=document.querySelector('[name="quarterly_price"]')?.value;
    var y=document.querySelector('[name="yearly_price"]')?.value;
    var dm=document.querySelector('[name="monthly_discount_percentage"]')?.value;
    var dq=document.querySelector('[name="quarterly_discount_percentage"]')?.value;
    var dy=document.querySelector('[name="yearly_discount_percentage"]')?.value;
    var gm=document.querySelector('[name="monthly_gst"]')?.value;
    var gq=document.querySelector('[name="quarterly_gst"]')?.value;
    var gy=document.querySelector('[name="yearly_gst"]')?.value;
    var mf=document.querySelector('[name="monthly_final_amount"]');
    var qf=document.querySelector('[name="quarterly_final_amount"]');
    var yf=document.querySelector('[name="yearly_final_amount"]');
    var qda=document.querySelector('[name="quarterly_discounted_amount"]');
    if(mf)mf.value=f(m,dm,gm);
    if(qf)qf.value=f(q,dq,gq);
    if(yf)yf.value=f(y,dy,gy);
    if(qda)qda.value=h(q,dq);
}
['monthly_price','quarterly_price','yearly_price','monthly_discount_percentage','quarterly_discount_percentage','yearly_discount_percentage','monthly_gst','quarterly_gst','yearly_gst'].forEach(function(n){var el=document.querySelector('[name="'+n+'"]');if(el){el.addEventListener('input',u);}});
window.addEventListener('load',u);
</script>
