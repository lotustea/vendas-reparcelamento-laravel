<div id='total-dividendos'>
    <h4></h4>
    <span></span>
</div>
<script>
$(function() {
    let headerValor = "Ver total a receber <button id='ver-total-dividendos' " +
        "class='btn btn-sm btn-info' " +
        "title='Ver dividendos'>" +
        "<i class='fa fa-eye'></i>" +
        "</button>"

    $(".box-header").find('h3').html(headerValor);

    $('#ver-total-dividendos').on('click', function() {
        $.ajax({
            dataType: 'html',
            type: 'get',
            url: 'total-dividendos',
            beforeSend: function() {
                $("#total-dividendos").find('h4').html("<i class='fa fa-spin fa-spinner'></i>");
                $("#total-dividendos").find('span').html('');
            },
            success: function (response) {
                $(".box-header").find('h3').html('Total a receber:');
                $("#total-dividendos").find('h4').html(response);
                $("#total-dividendos").find('span').html(
                    "<button id='esconder-dividendos' class='btn btn-sm btn-info' title='Esconder'>" +
                    "<i class='fa fa-eye-slash'></i>" +
                    "</button>"
                );
            }
        });
    });

});
</script>
