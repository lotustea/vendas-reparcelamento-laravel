
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
            'produtos' : $('#produtos').val()
        },
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        dataType:'json',
        success : function(data) {
            console.log(data)
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

