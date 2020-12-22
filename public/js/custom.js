$(function(){
    $('.save_city').click(function(){
        let elem = $(this);
        $(this).text('Please wait...').prop('disabled',true);
        if($('.new_city').val().trim() != ''){
            $.ajax({
                type: 'POST',
                url: window.route+'/city/save',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    city: $('.new_city').val()
                },
                success: function(result){
                    elem.text('Save City').prop('disabled',false);
                    if(result.status == 'error'){
                        $('.city_error').text(result.message);
                    }else{
                        let optionsArray = '<option value="">Select City</option>';
                        $.each(Object.keys(result.cities), function(key,value){
                            optionsArray += '<option value="'+value+'">'+result.cities[value]+'</option>';
                        });
                        $('#user_city').html(optionsArray);
                        $('#modal-default').modal('hide');
                        $('.new_city').val('');
                    }
                }
            });
        }else{
            alert('Please enter city name');
        }
    });

    $('.add_more_row').click(function(){
        let row = $('.email-ids .row:first').clone();
        $('.email-ids').append(row);
        $('.email-ids .row:last').find('input').val('');
    });

    $('body').on('click','.remove-email-row', function(){
        if($('.email-row').length > 1){
            $(this).parents('.email-row').remove();
        }
    });
});
