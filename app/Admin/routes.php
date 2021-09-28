<?php

use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
    'as'            => config('admin.route.prefix') . '.',
], function (Router $router) {

    $router->get('/', 'HomeController@index')->name('home');

    $router->resource('clientes', ClienteController::class);

    $router->resource('vendas', VendaController::class);

    $router->resource('venda-parcelas', VendaParcelaController::class);

    $router->resource('venda-produtos', VendaProdutoController::class);

    $router->resource('produtos', ProdutoController::class);

});
