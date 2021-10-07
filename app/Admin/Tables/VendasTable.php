<?php
namespace App\Admin\Tables;

use App\Models\Venda;
use App\Models\VendaParcela;
use Encore\Admin\Widgets\Table;
use Encore\Admin\Widgets\Tab;
use App\Admin\Helpers\Methods;

class VendasTable
{

    public function __construct($coluna)
    {
        $this->build($coluna);
    }

    /**
     * @param $coluna
     */
    public function build($coluna)
    {
        $coluna->expand(
            function ($model) {
                $vendas =
                    $model
                        ->vendas()
                        ->get()
                        ->map(function ($venda) {
                            $venda['acao'] = "
                                <a
                                    href=''
                                    class='btn btn-sm btn-info'
                                    title='Novo'
                                >
                                <i class='fa fa-eye'></i>
                                </a>";

                            $venda['entrada'] = 'R$' . (new Methods())->toReal($venda['entrada']);
                            $venda['data_compra'] = date( 'd/m/Y' , strtotime($venda['data_compra']));
                            $venda['parcelas'] .= 'x';
                            $venda['parcelas_pagas'] = $venda->parcelas()->where('status', '=', '1')->count();
                            $valorPago = $venda->parcelas()->sum('valor_pago');
                            $venda['valor_pago'] = 'R$' . (new Methods())->toReal($valorPago);
                            $valorExtra = $venda->parcelas()->sum('valor_extra');
                            $total = $valorExtra + $venda['total'];
                            $venda['valor_compra'] = 'R$' . (new Methods())->toReal($venda['total']);
                            $venda['total'] = 'R$' . (new Methods())->toReal($total);
                            $venda['valor_extra'] = '+ R$' . (new Methods())->toReal($valorExtra);
                            $valorFaltante = $total - $valorPago;
                            $venda['valor_faltante'] = 'R$' . (new Methods())->toReal($valorFaltante);

                                return
                                    $venda
                                        ->only([
                                            'acao',
                                            'data_compra',
                                            'valor_compra',
                                            'valor_extra',
                                            'total',
                                            'entrada',
                                            'valor_pago',
                                            'valor_faltante',
                                            'parcelas',
                                            'parcelas_pagas'
                                        ]);
                            });

                $tabela = new Table([
                    'Visualizar',
                    'Data da Compra',
                    'Valor da Compra',
                    'Valor Extra',
                    'Total',
                    'Entrada',
                    'Valor Pago',
                    'Valor Faltante',
                    'Parcelas',
                    'Parcelas Pagas'
                ],
                    $vendas->toArray()
                );

                return $tabela;
            }
        );
    }
}
