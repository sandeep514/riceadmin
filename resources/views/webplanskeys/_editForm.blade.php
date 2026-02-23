<div class="box-body">
    <div class="row">
        <div class="form-group col-md-6">
            <label>Plan Key</label>
            <input type="hidden" name="id" value="{{ $data->id }}">
            <input type="text" class="form-control" name="planKey" value="{{ $data->key ?? '' }}">
        </div>
        <div class="form-group col-md-6">
            <label>Status</label>
            <div style="padding-top: 7px;">
                @if(($data->status ?? 1) == 1)
                    <span class="label label-success">Active</span>
                    <a class="btn btn-sm btn-danger" href="{{ route('web.plans.keys.status.update', $data->id) }}" style="margin-left: 10px;">De-active</a>
                @else
                    <span class="label label-default">Inactive</span>
                    <a class="btn btn-sm btn-success" href="{{ route('web.plans.keys.status.update', $data->id) }}" style="margin-left: 10px;">Active</a>
                @endif
            </div>
        </div>
    </div>
</div>
