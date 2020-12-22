$(function(){
    $('.view_ports').click(function(){
        let date = $('input[name=date]').val();
        window.location.href = window.route+'/ports?date='+date;
    });

    $('.clear_ports').click(function(){
        window.location.href = window.route+'/ports';
    });
});
