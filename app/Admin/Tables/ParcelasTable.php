<?php
namespace App\Admin\Tables;

use Encore\Admin\Widgets\Table;

class ParcelasTable
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
                    return
                        $parcela
                            ->only([
                                'parcela',
                                'vencimento',
                                'valor_parcela',
                                'valor_pago',
                                'valor_abatido',
                                'valor_extra',
                                'pagamento',
                                'status'
                            ]);
                });

                $tabela = new Table([
                    'parcela',
                    'vencimento',
                    'valor_parcela',
                    'valor_pago',
                    'valor_abatido',
                    'valor_extra',
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
