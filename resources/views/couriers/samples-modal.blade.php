<a href="javascript:void(0)" data-toggle="modal" data-target="#sampleModal_{{ $model->id }}">View Samples</a>
<div class="modal fade" tabindex="-1" role="dialog" id="sampleModal_{{ $model->id }}">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Samples Details <small>(Scroll right to view all details)</small></h4>
            </div>
            <div class="modal-body" style="overflow: scroll;">
                <table class="table table-striped" style="width: 1000px;">
                    <thead>
                        <tr>
                            <th>Sr No.</th>
                            <th>Date</th>
                            <th>Supplier</th>
                            <th>Quality</th>
                            <th>Packing</th>
                            <th>Packing Type</th>
                            <th>No of Bags</th>
                            <th>Bags Qty</th>
                            <th>Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($samples as $key => $sample)
                            <tr>
                                <td>{{ $loop->index+1 }}</td>
                                <td>{{ $sample->date }}</td>
                                <td>{{ $sample->supplier_rel->name }}</td>
                                <td>
                                    <a href="javascript:void(0)"
                                       data-nameType="{{ ucwords(str_replace('-',' ',$sample->quality_rel->nameRel->type)) }}"
                                       data-name="{{ $sample->quality_rel->nameRel->name }}"
                                       data-formName="{{ $sample->quality_rel->formRel->form_name }}"
                                       data-type="{{ $sample->quality_rel->typeRel->name }}"
                                       class="view_quality_details"
                                       id="show_quality_modal_{{ $model->id }}"
                                    >View Quality</a>
                                </td>
                                <td>{{ $sample->packing_rel->code }}</td>
                                <td>{{ $sample->packing_type_rel->name }}</td>
                                <td>{{ ucfirst($sample->no_of_bags) }}</td>
                                <td>{{ $sample->bags_qty }}</td>
                                <td>{{ $sample->qty }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" id="qualityModal_{{ $model->id }}">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Quality Details</h4>
            </div>
            <div class="modal-body">
                <table class="table table-striped">
                    <tr>
                        <td><b>Rice: </b></td>
                        <td class="rice_type"></td>
                    </tr>
                    <tr>
                        <td><b>Name: </b></td>
                        <td class="rice_name"></td>
                    </tr>
                    <tr>
                        <td><b>Form Name: </b></td>
                        <td class="form_name"></td>
                    </tr>
                    <tr>
                        <td><b>Type: </b></td>
                        <td class="type"></td>
                    </tr>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function(){
        $('body').on('click','.view_quality_details',function(){
            let data = $(this).data();
            $('#qualityModal_{{ $model->id }}').find('.rice_type').html(data.nametype);
            $('#qualityModal_{{ $model->id }}').find('.rice_name').html(data.name);
            $('#qualityModal_{{ $model->id }}').find('.form_name').html(data.formname);
            $('#qualityModal_{{ $model->id }}').find('.type').html(data.type);
            $('#qualityModal_{{ $model->id }}').modal('show');
        });
    });
</script>
