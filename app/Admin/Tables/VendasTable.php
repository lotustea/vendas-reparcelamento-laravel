<?php
namespace App\Admin\Tables;

use Encore\Admin\Widgets\Table;
use App\Admin\Helpers\Methods as Methods;

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
                $vendas = $model->vendas()->get()->map(function ($venda) {
                $venda['acao'] = "
                    <a
                        href='http://localhost:8000/admin/clientes/create'
                        class='btn btn-sm btn-info'
                        title='Novo'
                    >
                    <i class='fa fa-eye'></i>
                    </a>";

                $venda['total'] = 'R$' . (new Methods())->toReal($venda['total']);
                $venda['entrada'] = 'R$' . (new Methods())->toReal($venda['entrada']);
                $venda['data_compra'] = date( 'd/m/Y' , strtotime($venda['data_compra']));
                $venda['parcelas'] .= 'x';

                    return
                        $venda
                            ->only([
                                'acao',
                                'data_compra',
                                'total',
                                'entrada',
                                'parcelas',
                            ]);
                });

                $tabela = new Table([
                    'Visualizar',
                    'Data da Compra',
                    'Total',
                    'Entrada',
                    'Parcelas',
                ],
                    $vendas->toArray()
                );

                return $tabela;
            }
        );
    }
}
