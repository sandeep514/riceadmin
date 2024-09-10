$(function(){
    $('select[name=name]').change(function(){
        window.location.href = window.route+'/live/price/'+$(this).val();
    });

    $('.check_for_na').on('ifChanged',function(event){
        if(event.target.checked){
            $(this).parents('.inputs').find('input').val('NA');
            $(this).parents('.inputs').find('select').val('stable');
        }else{
            $(this).parents('.inputs').find('input').val('');
            $(this).parents('.inputs').find('select').val('up');
        }
    });
});
