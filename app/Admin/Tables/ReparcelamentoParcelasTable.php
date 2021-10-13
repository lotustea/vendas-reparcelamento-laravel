<?php
namespace App\Admin\Tables;

use App\Admin\Helpers\Methods;
use Encore\Admin\Widgets\Table;

class ReparcelamentoParcelasTable
{

    public function __construct($coluna)
    {
        $this->build($coluna);
    }

    /**
     * @param $modelo
     */
    public function build($coluna)
    {
        $coluna->expand(
            function ($modelo) {
                $parcelas = $modelo->parcelas()->take(10)->get()->map(function ($parcela) {
                    $parcela['valor_total'] = 'R$ ' . Methods::toReal($parcela['valor_total']);
                    $parcela['valor_pago'] = 'R$ ' . Methods::toReal($parcela['valor_total']);
                    return
                        $parcela
                            ->only([
                                'numero_parcela',
                                'vencimento',
                                'valor_total',
                                'valor_pago',
                                'pagamento',
                                'status'
                            ]);
                });

                $tabela = new Table([
                    'numero_parcela',
                    'vencimento',
                    'valor_total',
                    'valor_pago',
                    'pagamento',
                    'status'
                ],
                    $parcelas->toArray()
                );

                return $tabela;
            }
        );
    }
}
