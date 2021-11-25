<?php

namespace App\Admin\Actions;

use App\Admin\Helpers\Methods;
use App\Models\Cliente;
use App\Models\Venda;
use App\Models\VendaParcela;
use Carbon\Carbon;
use Encore\Admin\Actions\Action;
use Encore\Admin\Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class CriarVendaAction extends Form
{
    public $name = 'Criar Venda';
    private $venda;

    public function criar(Request $request)
    {
        $cliente = $this->model();
        $valorTotal = Methods::toFloat($request->input('valor_total'));
        $entrada = Methods::toFloat($request->input('valor_entrada'));
        $parcelas = $request->input('parcelas');
        $vencimento = $request->input('primeiro_vencimento');

        try {
            $this->venda = $this->criarReparcelamento($cliente, $valorTotal, $entrada, $parcelas);
            $this->criarParcelas($valorTotal, $entrada, $vencimento, $parcelas);

            admin_success('Renegociação criada com sucesso!');
            return redirect('admin/reparcelamentos/'. $this->venda->id);
        } catch (Throwable $e) {
            admin_error('Erro ao criar renegociação', $e->getMessage());
        }
    }

    /**
     * Cria o venda a partir da negociação
     *
     * @param Cliente $cliente
     * @param $valorTotal
     * @param $entrada
     * @param $parcelas
     * @return Venda
     */
    private function criarReparcelamento($cliente, $valorTotal, $entrada, $parcelas): Venda
    {
        $novoReparcelamento = new Venda();
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
     * Cria as parcelas do venda
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
            $parcela = new VendaParcela();
            $parcela->reparcelamento_id = $this->venda->id;
            $parcela->numero_parcela = $i+1;
            $parcela->valor_total = $valorParcela;
            $parcela->vencimento = $vencimento;
            $parcela->status = 0;
            $parcela->save();

            $vencimento->addMonth(1);
        }
    }
}
