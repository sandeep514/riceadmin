$(function () {
    $('select[name=sntc_no]').change(function(){
        getSntcDetails($(this));
    });

    let isEdit = $('input[name=isEdit]').val();
    if(isEdit == 'true' || $('select[name=sntc_no]').val() != ''){
        getSntcDetails($('select[name=sntc_no]'));
    }

    // if(isEdit == 'false'){
    //     if($('.set_kett_null').is(':checked')){
    //         $('input[name=kett]').val('N/A').prop('disabled',true);
    //     }else{
    //         $('input[name=kett]').val('').prop('disabled',false);
    //     }
    // }

    $('.set_kett_null').on('ifChanged', function(){
        if($(this).is(':checked')){
            $('input[name=kett]').val('N/A').prop('disabled',true);
        }else{
            $('input[name=kett]').val('').prop('disabled',false);
        }
    });
});

function getSntcDetails(elem){
    $.ajax({
        type: 'GET',
        url: route+'/administrator/sample/register/'+elem.val(),
        data: {},
        success: function(result){
            $('.supplier-details').html(result.seller);
            $('.no_of_bags').html(capitalizeFirstLetter(result.no_of_bags));
            $('.bag_qty').html(result.qty_per_bag);
            $('.quality-details').html(result.quality_text);
        }
    });
}


function capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}
