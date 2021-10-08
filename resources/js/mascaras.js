$(function() {
    $('#valor_negociado{$id}').on('change', function (){
        let valorNegociado =  $('#valor_negociado{$id}').maskMoney('unmasked');
        let valorEntrada =  $('#valor_entrada{$id}').maskMoney('unmasked')
        alert(valorNegociado)
    })
});
