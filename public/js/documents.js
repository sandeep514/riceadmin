$(function(){
    $('.view_documents').click(function(){
        let contractNo = $('input[name=contract_no]').val();
        if(contractNo.trim() != ''){
            window.location.href = window.route+'/documents/'+contractNo;
        }
    });
});
