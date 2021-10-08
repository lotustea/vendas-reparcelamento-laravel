<?php

namespace App\Admin\Actions;

use App\Models\ClienteEmAtraso;
use App\Models\Reparcelamento;
use Encore\Admin\Actions\RowAction;
use Encore\Admin\Admin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class ClienteNegociarDividaAction extends RowAction
{
    public $name = 'Renegociar Dívidas';

    public function handle(Model $model, Request $request)
    {
        $id = $this->row()->getKey();
        $cliente = $model->find($id);

        DB::transaction(function () use ($cliente){
            $this->quitarParcelasMaisVendas($cliente);
        });
        return $this->response()->success('Success message.')->refresh();
    }

    public function form(Model $model)
    {
        $id = $this->row()->getKey();
        $cliente = $model->find($id);
        $totalEmdividas = $cliente->totalEmDividas(true);

        Admin::script(
                "$('#valor_negociado{$id}').maskMoney({
                  prefix:'R$ ',
                  allowNegative: true,
                  thousands:'.', decimal:',',
                  affixesStay: true});
            $('#valor_entrada{$id}').maskMoney({
                  prefix:'R$ ',
                  allowNegative: true,
                  thousands:'.', decimal:',',
                  affixesStay: true
            });
            $('#valor_total{$id}').maskMoney({
                  prefix:'R$ ',
                  allowNegative: true,
                  thousands:'.', decimal:',',
                  affixesStay: true
            });
        ");

        $this->select('cliente', 'Cliente',)
            ->options([
                $id => $cliente->nome . ' - ' . $totalEmdividas
            ])
            ->required()
            ->value($id);

        $this->date('primeiro_vencimento' . $id, 'Vencimento da primeira parcela')
            ->required()
            ->attribute(['autocomplete' => 'off']);

        $this->select('parcelas' . $id, 'Selecione a quantidade de parcelas')
            ->options([
                1 => '1x', 2 => '2x', 3 => '3x', 4 => '4x', 5 => '5x', 6 => '6x',
                7 => '7x', 8 => '8x', 9 => '9x', 10 => '10x', 11 => '11x', 12 => '12x',
                13 => '13x', 14 => '14x', 15 => '15x', 16 => '16x', 17 => '17x', 18 => '18x',
                19 => '19x', 20 => '20x', 21 => '21x', 22 => '22x', 23 => '23x', 24 => '24x',
            ])
            ->required()
            ->attribute(['autocomplete' => 'off']);

        $this->text('valor_negociado'. $id ,'Valor Negociação')
            ->attribute(['autocomplete' => 'off'])
            ->required()
            ->value($totalEmdividas);

        $this->text('valor_entrada'. $id , 'Entrada')
            ->attribute(['autocomplete' => 'off'])
            ->value('0,00');

        $this->text('valor_total'. $id , 'Total')
            ->required()
            ->attribute(['autocomplete' => 'off'])
            ->value($totalEmdividas);
    }

    /**
     * Busca e quita todas as parcelas e vendas em atraso do cliente
     * @param ClienteEmAtraso $cliente
     */
    private function quitarParcelasMaisVendas(ClienteEmAtraso $cliente)
    {
        $vendas = $cliente->vendas();
        foreach ($vendas as $venda) {
            $parcelas = $venda->parcelas();
            foreach ($parcelas as $parcela){
                $parcela->status = 1;
                $parcela->valor_pago = $parcela->valor_extra + $parcela->valor_total;
                $parcela->save();
            }
        }

    }

    /**
     * Cria o reparcelamento a partir da negociação
     *
     * @param ClienteEmAtraso $cliente
     * @param $valorTotal
     * @param $entrada
     * @param $vencimento
     * @param $parcelas
     */
    private function criarReparcelamento(ClienteEmAtraso $cliente, $valorTotal, $entrada, $parcelas)
    {
        $reparcelamento = new Reparcelamento();
        $reparcelamento->cliente = $cliente;
        $reparcelamento->entrada = $entrada;
        $reparcelamento->parcelas = $parcelas;
        $reparcelamento->valor_total = $valorTotal;


    }

    /**
     * Cria as parcelas do reparcelamento
     *
     * @param Reparcelamento $reparcelamento
     * @param $valorTotal
     * @param $entrada
     * @param $vencimento
     * @param $parcelas
     */
    private function criarParcelas(Reparcelamento $reparcelamento, $valorTotal, $entrada, $vencimento, $parcelas)
    {

    }

}
