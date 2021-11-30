
$('#valor_total').maskMoney({
    prefix:'R$ ',
    allowNegative: true,
    thousands:'.', decimal:',',
});
$('#valor_entrada').maskMoney({
    prefix:'R$ ',
    allowNegative: true,
    thousands:'.', decimal:',',
});
$('#valor_compra').maskMoney({
    prefix:'R$ ',
    allowNegative: true,
    thousands:'.', decimal:',',
});

function calcularValorCompra(){
    let valorCompra = $('#valor_compra');
    let valorEntrada = $('#valor_entrada');
    let valorTotal = $('#valor_total');
    $.ajax({
        url : '/admin/api/vendas/calcular-valor-compra',
        type : 'POST',
        data : {
            'produtos' : $(".produtos").select2("val")
        },
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        dataType: 'text',
        beforeSend: function () {
            valorCompra.val('Carregando...')
            valorTotal.val('Carregando...')
        },
        success : function(data) {
            valorCompra.maskMoney('mask', data)

            valorTotal.maskMoney('mask', valorCompra.maskMoney('unmasked')[0] - valorEntrada.maskMoney('unmasked')[0]);
        },
        error : function(request,error) {
            console.log("Request: "+JSON.stringify(request));
        }
    });
}

$(document).ready(function (){
    $('#modal-selector-produtos').on('hide.bs.modal', function () {
        calcularValorCompra()
    })
    $('.table').click(function() {
        calcularValorCompra()
    });
})

