<div class="box-body">
    <div class="row">
        <div class="form-group col-md-6">
            <label>Plan</label>
            <input type="text" class="form-control" name="plan" value="">
        </div>
        <div class="form-group col-md-6">
            <label>Amount</label>
            <input type="number" step="0.01" class="form-control" name="amount" value="" placeholder="Enter amount">
        </div>
    </div>
    <div class="row">
        <div class="form-group col-md-6">
            <label>Discount %</label>
            <input type="number" step="0.01" min="0" max="100" class="form-control" name="discount_percentage" value="" placeholder="Enter discount percentage">
        </div>
        <div class="form-group col-md-6">
            <label>Keys</label>
            <ul style="list-style: none;">
                @foreach($WebPlanKeysModel as $k => $v)
                    <li class="row">
                        <div class="col-md-6">
                            <p style="text-transform: capitalize;">{{  $v->key  }}</p>    
                        </div>
                        <div class="col-md-6">
                            <input type="checkbox" name="available[]" value="{{$v->id }}"  >
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>