<?php


use App\Admin\Actions\ClienteNegociarDividaAction;
use App\Admin\Actions\CriarVendaAction;
use App\Admin\Controllers\ReparcelamentoParcelaController;
use App\Admin\Forms\ReparcelarForm;
use App\Models\Cliente;
use App\Models\ClienteEmAtraso;
use Illuminate\Routing\Router;
use Illuminate\Http\Request;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
    'as'            => config('admin.route.prefix') . '.',
], function (Router $router) {

    $router->get('/', 'HomeController@index')->name('home');

    $router->get('total-dividendos', function () {
        return Cliente::totalDividendos(true);
    });
    $router->resource('clientes', ClienteController::class);
    $router->get('api/clientes', 'ClienteController@clientes');

    $router->resource('clientes-em-atraso', ClienteEmAtrasoController::class);

    $router->resource('vendas', VendaController::class);
    $router->post('api/vendas/calcular-valor-compra', 'VendaController@calcularValorCompra');
    $router->post('vendas/criar-venda',  function (Request $request) {
        $id = $request->input('cliente');
        $cliente = Cliente::find($id);
        return (new CriarVendaAction($cliente))->criar($request);
    });

    $router->resource('venda-parcelas', VendaParcelaController::class);

    $router->resource('venda-produtos', VendaProdutoController::class);

    $router->resource('produtos', ProdutoController::class);

    $router->resource('reparcelamentos', ReparcelamentoController::class);

    $router->get('reparcelamentos/criar/cliente/{id}', function ($id, \Encore\Admin\Layout\Content $content) {
        return (new App\Admin\Controllers\ReparcelamentoController())->criar($id, $content);
    });

    $router->post('reparcelamentos/criar-negociacao',  function (Request $request) {
        $id = $request->input('cliente');
        $cliente = Cliente::find($id);
        return (new ClienteNegociarDividaAction($cliente))->criar($request);
    });

    $router->resource('reparcelamento-parcelas', ReparcelamentoParcelaController::class);

});
