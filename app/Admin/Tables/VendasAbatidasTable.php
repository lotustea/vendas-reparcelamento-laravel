<?php

namespace App\Admin\Tables;

use App\Models\Venda;
use App\Models\VendaParcela;
use Encore\Admin\Widgets\Table;
use Encore\Admin\Widgets\Tab;
use App\Admin\Helpers\Methods;

class VendasAbatidasTable
{

    public function __construct($coluna)
    {
        $this->build($coluna);
    }

    /**
     * @param $coluna
     * @throws \JsonException
     */
    public function build($coluna)
    {
        $coluna->expand(
            function ($model) {
                $vendas =
                    collect(
                        json_decode($model->vendas_abatidas, true, 512, JSON_THROW_ON_ERROR)
                    )
                        ->map(function ($venda) {
                            $venda['acao'] = "
                                <a
                                    href='http://fabiwj.web282.uni5.net/editar-venda-{$venda['id']}'
                                    class='btn btn-sm btn-info'
                                    title='Novo'
                                >
                                <i class='fa fa-eye'></i>
                                </a>";

                            $venda['total'] = 'R$' . (new Methods())->toReal($venda['total']);
                            $venda['data_compra'] = date('d/m/Y', strtotime($venda['data_compra']));
                            $venda['parcelas'] .= 'x';

                            return
                                collect($venda)
                                    ->only([
                                        'acao',
                                        'data_compra',
                                        'total',
                                    ]);
                        });

                $tabela = new Table([
                    'Total',
                    'Data da venda',
                    'Visualizar',
                ],
                    $vendas->toArray()
                );

                return $tabela;
            }
        );
    }
}
