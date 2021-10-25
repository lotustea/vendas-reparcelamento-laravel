<?php

namespace App\Admin\Forms;

use App\Admin\Helpers\Methods;
use App\Models\Cliente;
use App\Models\Reparcelamento;
use App\Models\ReparcelamentoParcela;
use App\Models\Venda;
use App\Models\VendaParcela;
use Carbon\Carbon;
use Encore\Admin\Admin;
use Encore\Admin\Widgets\Form;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReparcelarForm extends Form
{
    public $name = 'Renegociar Dívidas';
    private $reparcelamento;
    private $id;

    public function handle(Request $request): \Encore\Admin\Actions\Response
    {

        $this->id = $request->input('cliente');
        $cliente = Cliente::find($this->id);
        $valorTotal = Methods::toFloat($request->input('valor_total'));
        $entrada = Methods::toFloat($request->input('valor_entrada'));
        $parcelas = $request->input('parcelas');
        $vencimento = $request->input('primeiro_vencimento');

        DB::beginTransaction();
        try {
            $this->reparcelamento = $this->criarReparcelamento($cliente, $valorTotal, $entrada, $parcelas);
            $this->criarParcelas($valorTotal, $entrada, $vencimento, $parcelas);
            $this->quitarParcelasMaisVendas($cliente);

            DB::commit();
            return $this->response()->success('Renegociação criada com sucesso!' );
        } catch (Throwable $e) {
            DB::rollback();
            return $this->response()->error($e);
        }
    }



    public function form(): void
    {
        $id = $this->data['id'];
        $cliente = Cliente::find($id);
        $totalEmdividas = $this->data['valor_negociado'];

        Admin::script(
            "$('#valor_negociado').maskMoney({
                  prefix:'R$ ',
                  allowNegative: true,
                  thousands:'.', decimal:',',
                  affixesStay: true});
            $('#valor_entrada').maskMoney({
                  prefix:'R$ ',
                  allowNegative: true,
                  thousands:'.', decimal:',',
                  affixesStay: true
            });
            $('#valor_total').maskMoney({
                  prefix:'R$ ',
                  allowNegative: true,
                  thousands:'.', decimal:',',
                  affixesStay: true
            });
        ");

        $this->radio('cliente', 'Cliente',)
            ->options([
                $id => $cliente->nome . ' - Valor total devido: ' . $totalEmdividas
            ])
            ->default($id)
            ->rules('required')->disable();

        $this->datetime('primeiro_vencimento', 'Vencimento da primeira parcela')
            ->placeholder('Selecione a data')
            ->format('DD-MM-YY')
            ->rules('required');

        $this->select('parcelas', 'Quantidade de parcelas')
            ->options([
                1 => '1x', 2 => '2x', 3 => '3x', 4 => '4x', 5 => '5x', 6 => '6x',
                7 => '7x', 8 => '8x', 9 => '9x', 10 => '10x', 11 => '11x', 12 => '12x',
                13 => '13x', 14 => '14x', 15 => '15x', 16 => '16x', 17 => '17x', 18 => '18x',
                19 => '19x', 20 => '20x', 21 => '21x', 22 => '22x', 23 => '23x', 24 => '24x',
            ])
            ->placeholder('Selecione a quantidade de parcelas')
            ->rules('required')
            ->attribute(['autocomplete' => 'off']);

        $this->text('valor_negociado','Valor Negociação')
            ->placeholder('Digite o valor negociado com o cliente')
            ->attribute(['autocomplete' => 'off'])
            ->rules('required');

        $this->text('valor_entrada', 'Entrada')
            ->placeholder('Digite o valor de entrada, se houver')
            ->attribute(['autocomplete' => 'off']);

        $this->text('valor_total', 'Total')
            ->placeholder('Valor total da negociação')
            ->rules('required')
            ->attribute(['autocomplete' => 'off']);
    }

    /**
     * Busca e quita todas as parcelas e vendas em atraso do cliente
     * @param Cliente $cliente
     */
    private function quitarParcelasMaisVendas(Cliente $cliente): void
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
     * @param Cliente $cliente
     * @param $valorTotal
     * @param $entrada
     * @param $parcelas
     * @return Reparcelamento
     */
    private function criarReparcelamento(Cliente $cliente, $valorTotal, $entrada, $parcelas): Reparcelamento
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
