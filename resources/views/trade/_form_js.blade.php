@section('javascript')
<script type="text/javascript">

    $(document).ready(function() {
        @include('trade._web_categories_select_all_js')
        @include('trade._web_trade_notification_js')
        @include('trade._trade_interest_users_js')
        $('select[name=tradeType]').change(function(event){
            let tradeType = $('select[name=tradeType] :selected').val();
            $.ajax({
                url : 'https://snjtradelink.com/staging/public/api/get/packing/by/'+tradeType,
                success : function (res){
                    $("select[name=ricepacking]").html('');
                    $("select[name=ricepacking]").append('<option value=""> Select </option>');
                    let objectKeys = Object.keys(res.data);

                    for(let i = 0; i < Object.keys(res.data).length ; i++){
                        $("select[name=ricepacking]").append('<option value="'+res.data[i].id+'"> '+res.data[i].packing+' '+res.data[i].description+' </option>');
                    }
                },
                error: function (err){
                    console.log(err);
                }
            })
        })
        $('select[name=category]').change(function(event){
            let riceCategory = $('select[name=category] :selected').val();
            console.log(riceCategory)
            $.ajax({
                url : 'https://snjtradelink.com/staging/public/api/get/rice/qualities/'+riceCategory,
                success : function (res){
                    $("select[name=quality]").html('');
                    $("select[name=quality]").append('<option value=""> Select </option>');
                    let objectKeys = res.data;

                    for(let i = 0; i < objectKeys.length ; i++){
                        $("select[name=quality]").append('<option value="'+objectKeys[i].id+'"> '+objectKeys[i].name+' </option>');
                    }
                },
                error: function (err){
                    console.log(err);
                }
            })
        })


        $('select[name=quality]').change(function(event){
            console.log("here")
            let riceCategory = $('select[name=quality] :selected').val();
            $.ajax({
                url : 'https://snjtradelink.com/staging/public/api/get/rice/qualities/name/' + riceCategory,
                success : function (res){
                    $("select[name=riceform]").html('');
                    $("select[name=riceform]").append('<option value=""> Select </option>');

                    console.log(res.data);
                    for(let i = 0; i < res.data.length ; i++){
                        $("select[name=riceform]").append('<option value="'+res.data[i].id+'"> '+res.data[i].name+' </option>');
                    }
                    for(let i = 0; i < res.riceform.length ; i++){
                        $("select[name=riceformLinkWithLivePrice]").append('<option value="'+res.riceform[i].id+'"> '+res.riceform[i].form_name+' </option>');
                    }


                },
                error: function (err){
                    console.log(err);
                }
            })            
        })


        // "get/rice/wand/" + riceNameId
        $('select[name=riceform]').change(function(event){
            console.log("here")
            let riceNameId = $('select[name=quality] :selected').val();
            $.ajax({
                url : 'https://snjtradelink.com/staging/public/api/get/rice/wand/' + riceNameId,
                success : function (res){
                    $("select[name=ricegrade]").html('');
                    $("select[name=ricegrade]").append('<option value=""> Select </option>');

                    console.log(res.data);
                    for(let i = 0; i < res.data.length ; i++){
                        $("select[name=ricegrade]").append('<option value="'+res.data[i].id+'"> '+res.data[i].get_wand_type['type']+' '+res.data[i]['value'] +'</option>');
                    }
                },
                error: function (err){
                    console.log(err);
                }
            })            
        })


        // 'get/seller/inr/packing'
        //     $.ajax({
        //         url : 'https://snjtradelink.com/staging/public/api/get/seller/inr/packing',
        //         success : function (res){
        //             $("select[name=ricepacking]").append('<option value=""> Select </option>');

        //             console.log(res.data);
        //             for(let i = 0; i < res.data.length ; i++){
        //                 $("select[name=ricepacking]").append('<option value="'+res.data[i].id+'"> '+res.data[i]['packing']+' </option>');
        //             }
        //         },
        //         error: function (err){
        //             console.log(err);
        //         }
        // })






    })

</script>
@endsection