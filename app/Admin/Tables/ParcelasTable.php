<?php
namespace App\Admin\Tables;

use App\Admin\Helpers\Methods;
use Carbon\Carbon;
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
                $parcelas = $modelo
                    ->parcelas()
                    ->take(40)
                    ->get()
                    ->map(
                        function ($parcela) {
                            $hoje = Carbon::now();
                            $vencimento = Carbon::parse($parcela['vencimento']);
                            if ($vencimento->gte($hoje)) {
                                $status = "Em aberto";
                            }else{
                                $status = "Em atraso";
                            }
                            $parcela['vencimento'] = date( 'd/m/Y' , strtotime($parcela['vencimento']));
                            $parcela['valor_parcela'] = 'R$' . (new Methods())->toReal($parcela['valor_parcela']);
                            $parcela['valor_pago'] = 'R$' . (new Methods())->toReal($parcela['valor_pago']);
                            $parcela['valor_abatido'] = 'R$' . (new Methods())->toReal($parcela['valor_abatido']);
                            $parcela['valor_extra'] = 'R$' . (new Methods())->toReal($parcela['valor_extra']);
                            $parcela['status'] = $parcela['status'] != 1 ? $status : 'Pago -  ' . date( 'd/m/Y' , strtotime($parcela['pagamento']));

                            return
                                $parcela
                                    ->only([
                                        'parcela',
                                        'vencimento',
                                        'valor_parcela',
                                        'valor_pago',
                                        'valor_abatido',
                                        'valor_extra',
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
                    'status'
                ],
                    $parcelas->toArray()
                );



                return $tabela;
            }
        );
    }
}
