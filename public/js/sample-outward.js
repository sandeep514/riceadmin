$(function(){
    $('select[name=sntc_no]').change(function(){
        $.ajax({
            type: 'GET',
            url: route+'/administrator/sample/register/'+$(this).val(),
            data: {},
            success: function(result){
                $('select[name=quality]').val(result.quality);
                $('select[name=bag_type]').val(result.packing_type);
                $('select[name=no_of_bags]').val(result.no_of_bags).change();
                $('input[name=qty_per_bag]').val(result.qty_per_bag);
            }
        });
    });

    $('.no_of_bags').change(function(){
        if($(this).val() == 'manual'){
            $('.qty_per_bag').show();
        }else{
            $('.qty_per_bag').hide();
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
        let totalQty = parseInt($('.packing').val());
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

})
