$(function () {
    $('select[name=sntc_no]').change(function(){
        if($(this).val() != ''){
            getSntcDetails($(this));
        }
    });

    let isEdit = $('input[name=isEdit]').val();
    if(isEdit == 'true' || $('select[name=sntc_no]').val() != ''){
        getSntcDetails($('select[name=sntc_no]'));
    }

    $('.set_kett_null').on('ifChanged', function(){
        if($(this).is(':checked')){
            $('input[name=kett]').val('N/A').prop('disabled',true);
        }else{
            $('input[name=kett]').val('').prop('disabled',false);
        }
    });

    $('.add_sub_mixture').click(function(){
        let clonedSubMix = $('.sub_mix:first').clone();
        $('.sub_mix_container').append(clonedSubMix);
        $('.sub_mix:last').find('input').val('');
        $('.add_sub_mixture:last').html('-').removeClass('add_sub_mixture').addClass('remove-submix').css('color','red');
    });

    $('body').on('click','.remove-submix', function(){
        $(this).parents('.sub_mix').remove();
    });
});

function getSntcDetails(elem){
    $.ajax({
        type: 'GET',
        url: route+'/administrator/deal/lab/'+elem.val(),
        data: {},
        success: function(result){
            $('.supplier-details').val(result.seller);
            $('.no_of_bags').val(capitalizeFirstLetter(result.no_of_bags));
            $('.bag_qty').val(result.qty_per_bag);
            $('.quality-details').val(result.quality_text);
            // console.log(result.lab_reports);
            if(result.lab_reports != null){
                $('.length').val(result.lab_reports.length);
                $('.ad_mixture').val(result.lab_reports.ad_mixture);
                $('.sub_ad_mixture').val(result.lab_reports.sub_ad_mixture);
                $('.moisture').val(result.lab_reports.moisture);
                $('.dd').val(result.lab_reports.dd);
                $('.broken').val(result.lab_reports.broken);
                $('.chalky').val(result.lab_reports.chalky);
                $('.kett').val(result.lab_reports.kett);
                $('.brown_layer').val(result.lab_reports.brown_layer);
                $('.stone').val(result.lab_reports.stone);
                $('.inmature').val(result.lab_reports.inmature);
                $('.broken_pin').val(result.lab_reports.broken_pin);
                $('.cooking').val(result.lab_reports.cooking);
            }else{
                $('.length').val('');
                $('.ad_mixture').val('');
                $('.sub_ad_mixture').val('');
                $('.moisture').val('');
                $('.dd').val('');
                $('.broken').val('');
                $('.chalky').val('');
                $('.kett').val('');
                $('.brown_layer').val('');
                $('.stone').val('');
                $('.inmature').val('');
                $('.broken_pin').val('');
                $('.cooking').val('');
            }
        },
        error: function(e){
            $('.sample-report').hide();
        }
    });
}


function capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}
