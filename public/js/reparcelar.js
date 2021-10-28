
function calculaValorTotal() {
    let calculo =
        parseFloat($('#valor_negociado').maskMoney('unmasked')[0])
        -
        parseFloat($('#valor_entrada').maskMoney('unmasked')[0])

    $('#valor_total').attr(
        'value',
        calculo
    );
    $('#valor_total').val(calculo)
    $('#valor_total').maskMoney('mask');
}

$('#valor_negociado').maskMoney({
    prefix:'R$ ',
    allowNegative: true,
    thousands:'.', decimal:',',
});
$('#valor_entrada').maskMoney({
    prefix:'R$ ',
    allowNegative: true,
    thousands:'.', decimal:',',
});
$('#valor_total').maskMoney({
    prefix:'R$ ',
    allowNegative: true,
    thousands:'.', decimal:',',
});

$(document).ready(function (){
    $('#valor_negociado').on('change', function () {
        calculaValorTotal()
    } )
    $('#valor_entrada').on('change', function () {
        calculaValorTotal()
    } )
})
$(document).ready(function (){
    $('#valor_entrada').on('change', function () {
        calculaValorTotal()
    } )
})
