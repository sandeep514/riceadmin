$(function(){
    $('body').on('click','.view_quality_details',function(){
        let data = $(this).data();
        $('.rice_type').html(data.nametype);
        $('.rice_name').html(data.name);
        $('.form_name').html(data.formname);
        $('.type').html(data.type);
        $('#qualityModal').modal('show');
    });
    $('.is_direct_deal').on('ifChanged', function(){
        if($(this).is(':checked')){
            $('.sntc_no').hide();
        }else{
            $('.sntc_no').show();
        }
    });
});
