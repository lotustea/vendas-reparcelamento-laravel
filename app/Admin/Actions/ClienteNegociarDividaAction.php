<?php

namespace App\Admin\Actions;

use App\Admin\Helpers\Methods;
use App\Models\Cliente;
use App\Models\Reparcelamento;
use App\Models\ReparcelamentoParcela;
use App\Models\Venda;
use App\Models\VendaParcela;
use Carbon\Carbon;
use Encore\Admin\Actions\Action;
use Encore\Admin\Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class ClienteNegociarDividaAction extends Form
{
    public $name = 'Renegociar Dívidas';
    private $reparcelamento;

    public function criar(Request $request)
    {
        $cliente = $this->model();
        $valorTotal = Methods::toFloat($request->input('valor_total'));
        $entrada = Methods::toFloat($request->input('valor_entrada'));
        $parcelas = $request->input('parcelas');
        $vencimento = $request->input('primeiro_vencimento');

        try {
            $this->reparcelamento = $this->criarReparcelamento($cliente, $valorTotal, $entrada, $parcelas);
            $this->criarParcelas($valorTotal, $entrada, $vencimento, $parcelas);
            $this->quitarParcelasMaisVendas($cliente);

            admin_success('Renegociação criada com sucesso!');
            return redirect('admin/reparcelamentos/'. $this->reparcelamento->id);
        } catch (Throwable $e) {
            admin_error('Erro ao criar renegociação', $e->getMessage());
        }
    }

    /**
     * Busca e quita todas as parcelas e vendas em atraso do cliente
     * @param Cliente $cliente
     */
    private function quitarParcelasMaisVendas($cliente): void
    {
        $vendas = $cliente->vendas();
        foreach ($vendas as $venda) {
            $venda = Venda::find($venda->id)->first();
            $parcelas = $venda->parcelas();
            $venda->status = 1;
            $venda->save();
            foreach ($parcelas as $parcela){
                $parcela = VendaParcela::find($parcela->id)->first();
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
    private function criarReparcelamento($cliente, $valorTotal, $entrada, $parcelas): Reparcelamento
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
            $parcela->numero_parcela = $i+1;
            $parcela->valor_total = $valorParcela;
            $parcela->vencimento = $vencimento;
            $parcela->status = 0;
            $parcela->save();

            $vencimento->addMonth(1);
        }
    }
}
