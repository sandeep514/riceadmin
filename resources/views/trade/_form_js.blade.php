@section('javascript')
<script type="text/javascript">

    $(document).ready(function() {
        const apiBase = @json(url('/api'));
        const convertPrefill = @json($convertPrefill ?? null);

        @include('trade._web_categories_select_all_js')
        @include('trade._web_trade_notification_js')
        @include('trade._trade_interest_users_js')

        @if(!empty($convertPrefill ?? null))
        (function () {
            const prefill = convertPrefill;
            const preQualityType = prefill.quality_type;
            const preQuality = prefill.quality;
            const preRiceForm = prefill.quality_form;
            const preGrade = prefill.grade;

            if (preQualityType !== null && preQualityType !== '' && preQuality) {
                $.ajax({
                    url: apiBase + '/get/rice/qualities/' + preQualityType,
                    success: function (res) {
                        $("select[name=quality]").html('');
                        $("select[name=quality]").append('<option value=""> Select </option>');
                        for (let i = 0; i < res.data.length; i++) {
                            const selected = (String(preQuality) === String(res.data[i].id)) ? 'selected' : '';
                            $("select[name=quality]").append('<option value="' + res.data[i].id + '" ' + selected + '> ' + res.data[i].name + ' </option>');
                        }
                    },
                    error: function (err) {
                        console.log(err);
                    }
                });

                $.ajax({
                    url: apiBase + '/get/rice/qualities/name/' + preQuality,
                    success: function (res) {
                        $("select[name=riceform]").html('');
                        $("select[name=riceform]").append('<option value=""> Select </option>');
                        for (let i = 0; i < res.data.length; i++) {
                            const selected = (String(preRiceForm) === String(res.data[i].id)) ? 'selected' : '';
                            $("select[name=riceform]").append('<option value="' + res.data[i].id + '" ' + selected + '> ' + res.data[i].name + ' </option>');
                        }
                        $("select[name=riceformLinkWithLivePrice]").html('');
                        $("select[name=riceformLinkWithLivePrice]").append('<option>Select any</option>');
                        for (let i = 0; i < res.riceform.length; i++) {
                            $("select[name=riceformLinkWithLivePrice]").append('<option value="' + res.riceform[i].id + '"> ' + res.riceform[i].form_name + ' </option>');
                        }
                    },
                    error: function (err) {
                        console.log(err);
                    }
                });

                $.ajax({
                    url: apiBase + '/get/rice/wand/' + preQuality,
                    success: function (res) {
                        $("select[name=ricegrade]").html('');
                        $("select[name=ricegrade]").append('<option value=""> Select </option>');
                        for (let i = 0; i < res.data.length; i++) {
                            const selected = (String(preGrade) === String(res.data[i].id)) ? 'selected' : '';
                            $("select[name=ricegrade]").append('<option value="' + res.data[i].id + '" ' + selected + '> ' + res.data[i].get_wand_type.type + ' ' + res.data[i].value + ' </option>');
                        }
                    },
                    error: function (err) {
                        console.log(err);
                    }
                });
            }
        })();
        @endif

        $('select[name=tradeType]').change(function(event){
            let tradeType = $('select[name=tradeType] :selected').val();
            if (!tradeType) {
                return;
            }
            const selectedPacking = convertPrefill && convertPrefill.ricepacking != null && convertPrefill.ricepacking !== ''
                ? String(convertPrefill.ricepacking)
                : '';
            $.ajax({
                url : apiBase + '/get/packing/by/' + tradeType,
                success : function (res){
                    $("select[name=ricepacking]").html('');
                    $("select[name=ricepacking]").append('<option value=""> Select </option>');

                    for(let i = 0; i < res.data.length ; i++){
                        const optionSelected = selectedPacking !== '' && String(res.data[i].id) === selectedPacking ? 'selected' : '';
                        const description = res.data[i].description ? (' ' + res.data[i].description) : '';
                        $("select[name=ricepacking]").append('<option value="'+res.data[i].id+'" '+optionSelected+'> '+res.data[i].packing+description+' </option>');
                    }
                },
                error: function (err){
                    console.log(err);
                }
            })
        })

        @if(isset($defaultTradeType) && $defaultTradeType !== null && $defaultTradeType !== '')
        $('select[name=tradeType]').trigger('change');
        @endif
        $('select[name=category]').change(function(event){
            let riceCategory = $('select[name=category] :selected').val();
            console.log(riceCategory)
            $.ajax({
                url : apiBase + '/get/rice/qualities/' + riceCategory,
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
                url : apiBase + '/get/rice/qualities/name/' + riceCategory,
                success : function (res){
                    $("select[name=riceform]").html('');
                    $("select[name=riceform]").append('<option value=""> Select </option>');

                    console.log(res.data);
                    for(let i = 0; i < res.data.length ; i++){
                        $("select[name=riceform]").append('<option value="'+res.data[i].id+'"> '+res.data[i].name+' </option>');
                    }
                    $("select[name=riceformLinkWithLivePrice]").html('');
                    $("select[name=riceformLinkWithLivePrice]").append('<option>Select any</option>');
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
                url : apiBase + '/get/rice/wand/' + riceNameId,
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
    })

</script>
@endsection
