$(function(){
    $('.no_of_bags').change(function(){
        if($(this).val() == 'manual'){
            if($('.packing').val() == ''){
                $('.bags_qty_input').prop('disabled',true).attr('placeholder','Please select packing first');
            }else{
                $('.bags_qty_input').prop('disabled',false).attr('placeholder','Enter bags qty');
            }
            $('.bags_qty').show();
        }else{
            $('.bags_qty').hide();
        }
    });
    $('.packing').change(function(){
        if($(this).val() != ''){
            $('.bags_qty_input').prop('disabled',false).attr('placeholder','Enter bags qty');
        }else{
            $('.bags_qty_input').prop('disabled',true).attr('placeholder','Please select packing first');
        }
    });
    $('.bags_qty_input').keyup(function(){
        let totalQty = parseInt($('.packing option:selected').data('code'));
        totalQty = totalQty * parseInt($(this).val());
        $('.qty').val(totalQty);
    });

    $('body').on('click','.view_quality_details',function(){
        let data = $(this).data();
        $('.rice_type').html(data.nametype);
        $('.rice_name').html(data.name);
        $('.form_name').html(data.formname);
        $('.type').html(data.type);
        $('#qualityModal').modal('show');
    });

});
