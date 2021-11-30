var valorCompra = $('#valor_compra');
var valorEntrada = $('#valor_entrada');
var valorTotal = $('#valor_total');

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
            valorCompra.val(data)

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
    $('#valor_entrada').on('change', function() {
        valorTotal.maskMoney('mask', valorCompra.maskMoney('unmasked')[0] - valorEntrada.maskMoney('unmasked')[0]);
    });
    $('#valor_entrada').on('keyup', function() {
        valorTotal.maskMoney('mask', valorCompra.maskMoney('unmasked')[0] - valorEntrada.maskMoney('unmasked')[0]);
    });

})

