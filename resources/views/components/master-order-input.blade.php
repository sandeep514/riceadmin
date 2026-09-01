<form method="POST" action="{{ route($route) }}" class="form-inline">
    @csrf
    <input type="hidden" name="id" value="{{ $model->id }}">
    <div class="input-group input-group-sm" style="width:130px;">
        <input type="number"
               name="order_no"
               class="form-control"
               min="1"
               value="{{ $model->order_no }}"
               required>
        <span class="input-group-btn">
            <button type="submit" class="btn btn-info" title="Change order">
                <i class="fa fa-save"></i>
            </button>
        </span>
    </div>
</form>
