<?php

namespace App\Admin\Actions;

use App\Admin\Helpers\Methods;
use App\Models\Cliente;
use App\Models\Produto;
use App\Models\Venda;
use App\Models\VendaParcela;
use App\Models\VendaProduto;
use Carbon\Carbon;
use Encore\Admin\Actions\Action;
use Encore\Admin\Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class CriarVendaAction extends Form
{
    private $venda;

    public function criar(Request $request)
    {
        $cliente = $this->model();
        $valorCompra = $this->calcularValorCompra(collect($request->input('produtos')));
        $entrada = Methods::toFloat($request->input('valor_entrada'));
        $valorTotal = (float)$valorCompra - (float)$entrada;
        $parcelas = $request->input('parcelas');
        $vencimento = $request->input('primeiro_vencimento');
        $produtos = $request->input('produtos');

        try {
            $this->venda = $this->criarVenda($cliente, $produtos, $valorTotal, $entrada, $parcelas);
            $this->criarParcelas($valorTotal, $entrada, $vencimento, $parcelas);

            admin_success('Venda criada com sucesso!');
            return redirect('admin/vendas/'. $this->venda->id);
        } catch (Throwable $e) {
            admin_error('Erro ao criar venda', $e->getMessage());
        }
    }

    private function calcularValorCompra ($produtos) {
        $valorCompra = 00.00;

        if (isset($produtos)){
            $valorCompra = (float)Produto::find($produtos)->sum('preco');
        }

        return Methods::toFloat($valorCompra);
    }

    /**
     * Cria a venda a partir da negociação
     *
     * @param $cliente
     * @param $valorTotal
     * @param $entrada
     * @param $parcelas
     * @return Venda
     */
    private function criarVenda($cliente, $produtos, $valorTotal, $entrada, $parcelas): Venda
    {
        $novaVenda = new Venda();
        $novaVenda->id_cliente = $cliente->id;
        $novaVenda->entrada = $entrada;
        $novaVenda->parcelas = $parcelas;
        $novaVenda->total = $valorTotal;
        $novaVenda->status = 0;
        $novaVenda->data_compra = Carbon::today()->format('Y-m-d');
        $novaVenda->save();

        foreach ($produtos as $produto){
            if (isset($produto)) {
                $vendaProduto = new VendaProduto();
                $vendaProduto->id_venda = $novaVenda->id;
                $vendaProduto->id_produto = $produto;
                $vendaProduto->quantidade = 1;
                $vendaProduto->save();
            }
        }

        return $novaVenda;
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
            $parcela->id_venda = $this->venda->id;
            $parcela->parcela = $i+1;
            $parcela->valor_parcela = $valorParcela;
            $parcela->vencimento = $vencimento;
            $parcela->status = 0;
            $parcela->save();

            $vencimento->addMonth(1);
        }
    }
}
