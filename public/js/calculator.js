(function ($) {
    function parseCalcNumber(value) {
        if (value === '' || value === null || value === undefined) {
            return 0;
        }
        return parseFloat(String(value).replace(/,/g, '')) || 0;
    }

    function runCalculator() {
        var ricemin = parseCalcNumber($('#ricemin').val());
        var ricemax = parseCalcNumber($('#ricemax').val());
        var transportmin = parseCalcNumber($('#transportmin').val());
        var transportmax = parseCalcNumber($('#transportmax').val());
        var category = parseCalcNumber($('#category').val());
        var charges = parseCalcNumber($('#charges').val());
        var dollarrate = parseCalcNumber($('#dollarrate').val());
        var percentageValue = parseCalcNumber($('#percentage').val());
        var hasPercentage = $('#percentage').val() !== '';

        if (ricemax < ricemin) {
            alert('Rice max price should be greater than Rice min price.');
            return false;
        }
        if (transportmax < transportmin) {
            alert('Transport max price should be greater than Transport min price.');
            return false;
        }

        if (ricemin != 0 && ricemax != 0 && transportmin != 0 && transportmax != 0 && category != 0 && dollarrate != 0 && hasPercentage && charges != 0) {
            var totalMin = parseFloat(parseFloat(ricemin) + parseFloat(category) + parseFloat(transportmin) + parseFloat(charges)).toFixed(2);
            var totalMax = parseFloat(parseFloat(ricemax) + parseFloat(category) + parseFloat(transportmax) + parseFloat(charges)).toFixed(2);
            var exchangeRatemin = parseFloat(totalMin / dollarrate).toFixed(2);
            var exchangeRatemax = parseFloat(totalMax / dollarrate).toFixed(2);
            var Fobmin = Math.round((parseFloat(((exchangeRatemin * percentageValue) / 100).toFixed(2)) + parseFloat(parseFloat(exchangeRatemin).toFixed(2))).toFixed(2));
            var Fobmax = Math.round((parseFloat(((exchangeRatemax * percentageValue) / 100).toFixed(2)) + parseFloat(parseFloat(exchangeRatemax).toFixed(2))).toFixed(2));

            $('#total').html('₹' + Math.round(totalMin) + ' - ₹' + Math.round(totalMax));
            $('#exchangeRate').html('$' + Math.round(exchangeRatemin) + ' - $' + Math.round(exchangeRatemax));
            $('#fob').html('$' + Math.round(Fobmin) + ' - $' + Math.round(Fobmax));
            return true;
        }

        if (ricemin != 0 && ricemax == 0 && transportmin != 0 && transportmax != 0 && category != 0 && dollarrate != 0 && hasPercentage && charges != 0) {
            var totalMinOnly = parseFloat(parseFloat(ricemin) + parseFloat(category) + parseFloat(transportmin) + parseFloat(charges)).toFixed(2);
            var exchangeRateminOnly = parseFloat(totalMinOnly / dollarrate).toFixed(2);
            var FobminOnly = Math.round((parseFloat(((exchangeRateminOnly * percentageValue) / 100).toFixed(2)) + parseFloat(parseFloat(exchangeRateminOnly).toFixed(2))).toFixed(2));

            $('#total').html('₹' + Math.round(totalMinOnly) + ' - ₹0');
            $('#exchangeRate').html('$' + Math.round(exchangeRateminOnly) + ' - $0');
            $('#fob').html('$' + Math.round(FobminOnly) + ' - $0');
            return true;
        }

        if (ricemin == 0 && ricemax != 0 && transportmin != 0 && transportmax != 0 && category != 0 && dollarrate != 0 && hasPercentage && charges != 0) {
            var totalMaxOnly = parseFloat(parseFloat(ricemax) + parseFloat(category) + parseFloat(transportmax) + parseFloat(charges)).toFixed(2);
            var exchangeRatemaxOnly = parseFloat(totalMaxOnly / dollarrate).toFixed(2);
            var FobmaxOnly = Math.round((parseFloat(((exchangeRatemaxOnly * percentageValue) / 100).toFixed(2)) + parseFloat(parseFloat(exchangeRatemaxOnly).toFixed(2))).toFixed(2));

            $('#total').html('₹0 - ₹' + Math.round(totalMaxOnly));
            $('#exchangeRate').html('$0 - $' + Math.round(exchangeRatemaxOnly));
            $('#fob').html('$0 - $' + Math.round(FobmaxOnly));
            return true;
        }

        return true;
    }

    $(document).ready(function () {
        $('.calculate').off('click');
        $(document).off('click.calculator', '.calculate').on('click.calculator', '.calculate', function (e) {
            e.preventDefault();
            e.stopPropagation();
            runCalculator();
        });
    });
})(jQuery);
