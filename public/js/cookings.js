$(function () {
    $('select[name=sntc_no]').change(function(){
        getSntcDetails($(this));
    });

    let isEdit = $('input[name=isEdit]').val();
    if(isEdit == 'true'){
        getSntcDetails($('select[name=sntc_no]'));
    }

    $(document).ready(function(){
        $('.loader-cooking').hide();
    });
});


function getSntcDetails(elem){
    $('.loader').show();
    $.ajax({
        type: 'GET',
        url: route+'/administrator/sample/register/'+elem.val(),
        data: {},
        success: function(result){
            $('.supplier-details').val(result.seller);
            $('.no_of_bags').val(capitalizeFirstLetter(result.no_of_bags));
            $('.bag_qty').val(result.qty_per_bag);
            $('.quality-details').val(result.quality_text);
            $('.loader').hide();
        }
    });
}


function capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}
