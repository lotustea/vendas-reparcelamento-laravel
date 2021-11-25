<?php

namespace App\Admin\Actions;

use App\Admin\Controllers\ReparcelamentoController;
use App\Admin\Helpers\Methods;
use App\Models\Reparcelamento;
use App\Models\Venda;
use Carbon\Carbon;
use Encore\Admin\Actions\BatchAction;
use Encore\Admin\Actions\RowAction;
use Encore\Admin\Admin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReparcelamentoParcelaPagarAction extends RowAction
{
    public $name = 'Pagamento de parcela';

    public function handle(Model $model, Request $request)
    {
        $id = $this->row()->getKey();
        $parcela = $model->find($id);
        $valorPagamento = Methods::toFloat($request->input('valor_pagamento' . $id));

        if ($valorPagamento < $parcela->valor_parcela) {
            return $this->response()->error('Erro. Valor do pagamento é inferior ao valor da parcela!');
        }

        $reparcelamento = $parcela->reparcelamento()->get();

        $parcela->status = 1;
        $parcela->valor_pago = $valorPagamento;
        $parcela->data_pagamento = Carbon::now();
        $parcela->save();

        if ($parcela->numero_parcela === $reparcelamento[0]->parcelas) {
            $reparcelamento = Reparcelamento::find($reparcelamento[0]->id);
            $reparcelamento->status = 1;
            $reparcelamento->save();
        }


        return $this->response()->success('Pagamento efetuado com sucesso!' )->refresh();

    }

    public function form(Model $model): void
    {
        $id = $this->row()->getKey();

        Admin::script(
            "$('#valor_pagamento{$id}').maskMoney({
                  prefix:'R$ ',
                  allowNegative: true,
                  thousands:'.', decimal:',',
                  affixesStay: true});
        ");

        $totalParcela = 'R$ ' . $model->find($id)->valor_total;
        $numeroParcela = $model->find($id)->numero_parcela;

        $this->text('numero_parcela'. $id ,'Parcela de número')
            ->attribute(['autocomplete' => 'off'])
            ->value($numeroParcela)
            ->disable();

        $this->text('total_parcela'. $id ,'Total da parcela')
            ->attribute(['autocomplete' => 'off'])
            ->value($totalParcela)
            ->disable();

        $this->text('valor_pagamento'. $id ,'Valor à pagar')
            ->attribute(['autocomplete' => 'off'])
            ->required();

    }

    public function display($value)
    {
        return "<i class=\"fa fa-money\"></i>";
    }

}
