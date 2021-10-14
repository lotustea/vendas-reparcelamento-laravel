<?php

namespace App\Admin\Actions;

use App\Admin\Helpers\Methods;
use App\Models\ClienteEmAtraso;
use App\Models\Reparcelamento;
use App\Models\ReparcelamentoParcela;
use App\Models\Venda;
use App\Models\VendaParcela;
use Encore\Admin\Actions\RowAction;
use Encore\Admin\Admin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Throwable;


class ClienteNegociarDividaAction extends RowAction
{
    public $name = 'Renegociar Dívidas';
    private $reparcelamento;

    public function handle(Model $model, Request $request): \Encore\Admin\Actions\Response
    {
        $id = $this->row()->getKey();
        $cliente = $model->find($id);
        $valorTotal = Methods::toFloat($request->input('valor_total' . $id));
        $entrada = Methods::toFloat($request->input('valor_entrada' . $id));
        $parcelas = $request->input('parcelas' . $id);
        $vencimento = $request->input('primeiro_vencimento' . $id);

        DB::beginTransaction();
        try {
                $this->reparcelamento = $this->criarReparcelamento($cliente, $valorTotal, $entrada, $parcelas);
                $this->criarParcelas($valorTotal, $entrada, $vencimento, $parcelas);
                $this->quitarParcelasMaisVendas($cliente);

            DB::commit();
            return $this->response()->success('Renegociação criada com sucesso!' )->refresh();
        } catch (Throwable $e) {
            DB::rollback();
            return $this->response()->error($e);
        }
    }

    public function form(Model $model): void
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
    private function quitarParcelasMaisVendas(ClienteEmAtraso $cliente): void
    {
        $vendas = $cliente->vendas();
        foreach ($vendas as $venda) {
            $venda = Venda::find($venda->id);
            $parcelas = $venda->parcelas();
            $venda->status = 1;
            $venda->save();
            foreach ($parcelas as $parcela){
                $parcela = VendaParcela::find($parcela->id);
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
     * @param $parcelas
     * @return Reparcelamento
     */
    private function criarReparcelamento(ClienteEmAtraso $cliente, $valorTotal, $entrada, $parcelas): Reparcelamento
    {
        $novoReparcelamento = new Reparcelamento();
        $novoReparcelamento->cliente_id = $cliente->id;
        $novoReparcelamento->entrada = $entrada;
        $novoReparcelamento->parcelas = $parcelas;
        $novoReparcelamento->valor_total = $valorTotal;
        $novoReparcelamento->vendas_abatidas = $cliente->vendas()->get()->toJson();
        $novoReparcelamento->status = 0;
        $novoReparcelamento->save();

        return $novoReparcelamento;
    }

    /**
     * Cria as parcelas do reparcelamento
     *
     * @param $valorTotal
     * @param $entrada
     * @param $vencimento
     * @param $parcelas
     */
    private function criarParcelas($valorTotal, $entrada, $vencimento, $parcelas): void
    {
        $valorTotal -= $entrada;
        $valorDivisao = $valorTotal / $parcelas;
        $valorMultiplicacao = $parcelas * $valorDivisao;
        $restante = $valorTotal - $valorMultiplicacao;
        $valorParcela = ($valorTotal + $restante) / $parcelas;

        $vencimento = Carbon::parse($vencimento);
        for ($i = 0; $i < $parcelas; $i++) {
            $parcela = new ReparcelamentoParcela();
            $parcela->reparcelamento_id = $this->reparcelamento->id;
            $parcela->numero_parcela = $i;
            $parcela->valor_total = $valorParcela;
            $parcela->vencimento = $vencimento;
            $parcela->status = 0;
            $parcela->save();

            $vencimento->addMonth(1);
        }
    }

}
