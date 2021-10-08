<?php

use App\Models\ClienteEmAtraso;
use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
    'as'            => config('admin.route.prefix') . '.',
], function (Router $router) {

    $router->get('/', 'HomeController@index')->name('home');

    $router->get('total-dividendos', function () {
        return ClienteEmAtraso::totalDividendos(true);
    });

    $router->resource('clientes-em-atraso', ClienteEmAtrasoController::class);

    $router->resource('vendas', VendaController::class);

    $router->resource('venda-parcelas', VendaParcelaController::class);

    $router->resource('venda-produtos', VendaProdutoController::class);

    $router->resource('produtos', ProdutoController::class);

    $router->resource('reparcelamentos', ReparcelamentoController::class);

    $router->get('reparcelamentos/novo/{id}', 'ReparcelamentoController@criar');

    $router->resource('reparcelamento-parcelas', ReparcelamentoParcelaController::class);

});
