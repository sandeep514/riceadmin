$(function(){
    $('.view_modules').click(function(){
        if($('.role').val() == ''){
            toastr.error('Please select the role first','Error')
            return false;
        }
        window.location.href=route+'/administrator/modules/'+$('.role').val();
    });
});
