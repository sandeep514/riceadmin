<div class="box-body">
    <div class="row margin-top-10">
        <div class="col-md-12">
            <div class="group-panel">
                <label class="group-title">Create Plan</label>
                <div class="group-content">
                    <div class="row">
                        <div class="col-md-12">
                            {{-- Role --}}
                            <div class="col-md-6" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('role_id', 'Role') !!}
                                <select name="role_id" id="role_id" class="form-control" required>
                                    <option value="">Select Role</option>
                                    @foreach($roles as $id => $name)
                                        <option value="{{ $id }}" {{ (isset($plan) && $plan->first()->role_id == $id) ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Category (dependent on role) --}}
                            <div class="col-md-6" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('category_id', 'Role Category') !!}
                                <select name="category_id" id="category_id" class="form-control" required>
                                    <option value="">Select Role first</option>
                                </select>
                                <small class="help-block">Select a role first to load categories</small>
                            </div>

                            {{-- Plan Name (auto-generated, readonly) --}}
                            <div class="col-md-12" style="margin-bottom: 20px;padding-left: 0">
                                {!! Form::label('plan_name','Plan Name') !!}
                                {!! Form::text('plan_name', null ,['class'=>'form-control', 'id'=>'plan_name', 'readonly' => 'readonly', 'placeholder' => 'Auto-generated from Role + Category']) !!}
                                <small class="help-block">Automatically generated as <em>role_category</em> (e.g. buyer_broker)</small>
                            </div>
                            <div class="col-md-12">
                                <div class="row">
                                    <h3 style="font-size: 18px;margin-left: 13px;font-weight: 600;">Chart Intervals</h3>
                                    @foreach( $ChartInterval as $k => $v )
                                        @if($k > 0)
                                        <div class="col-md-2">
                                            {!! Form::checkbox('chartint['.$v->id.']', $v->id ,['class'=>'form-control']) !!}
                                            {!! Form::label( 'chartint',$v->name ) !!}
                                        </div>
                                        @endif
                                    @endforeach   
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    
                    <div class="box-body">
                        <div class="row margin-top-10">
                            <div class="col-md-12">
                                <div class="group-panel">
                                    <label class="group-title">Sub Plan</label>
                                    <div class="group-content">
                                        
                                        <div class="row">
                                            <!--<h3 style="font-size: 18px;margin-left: 13px;font-weight: 600;">Sub Plan</h3>-->
                                            @foreach( $SubPlan as $k => $v )
                                                <div class="col-md-4">
                                                    <!--{!! Form::checkbox('subplan[]',$v->id,['checked' => 'false' , 'class'=>'form-control']) !!}-->
                                                    {!! Form::label('subplan[]', $v->name) !!}
                                                    
                                                    {!! Form::label('subplan['.$v->id.']','Plan Price') !!}
                                                    {!! Form::text('subplan['.$v->id.']', null ,['placeholder' => 'Rs' ,'class'=>'form-control']) !!}
                                                    
                                                </div>
                                            @endforeach
                                        </div>
                                        
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    
                    
                    
                    
                    
                  
                    
                </div>
            </div>
        </div>
    </div>
</div>

@section('javascript')
<script>
$(document).ready(function() {
    // Slugify helper: lowercase + underscores
    function slugify(text) {
        return text.toString().toLowerCase().trim()
            .replace(/\s+/g, '_')
            .replace(/[^\w_]/g, '');
    }

    function updatePlanName() {
        var roleText = $('#role_id option:selected').text().trim();
        var categoryText = $('#category_id option:selected').text().trim();

        if (roleText && roleText !== 'Select Role' && categoryText && categoryText !== 'Select Role first' && categoryText !== 'No categories available' && categoryText !== 'Select Category') {
            $('#plan_name').val(slugify(roleText) + '_' + slugify(categoryText));
        } else {
            $('#plan_name').val('');
        }
    }

    // Load categories when role changes
    $('#role_id').on('change', function() {
        var roleId = $(this).val();
        var categorySelect = $('#category_id');

        categorySelect.empty().append('<option value="">Loading...</option>');
        $('#plan_name').val('');

        if (roleId) {
            $.ajax({
                url: "web-access/get-categories",
                type: "GET",
                data: { role_id: roleId },
                success: function(data) {
                    categorySelect.empty().append('<option value="">Select Category</option>');
                    if (Object.keys(data).length > 0) {
                        $.each(data, function(key, value) {
                            categorySelect.append('<option value="' + key + '">' + value + '</option>');
                        });
                    } else {
                        categorySelect.append('<option value="">No categories available</option>');
                    }
                },
                error: function() {
                    categorySelect.empty().append('<option value="">Error loading categories</option>');
                }
            });
        } else {
            categorySelect.empty().append('<option value="">Select Role first</option>');
        }
    });

    // Update plan name when category changes
    $('#category_id').on('change', function() {
        updatePlanName();
    });
});
</script>
@endsection