<div class="box-body">
    <div class="row">
        <div class="form-group col-md-6">
            <label>Plan</label>
            <input type="text" class="form-control" name="plan" value="">
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