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

class VendaParcelaPagarAction extends RowAction
{
    public $name = 'Pagamento de parcela';

    public function handle(Model $model, Request $request)
    {
        $id = $this->row()->getKey();
        $parcela = $model->find($id);
        $valorPagamento = Methods::toFloat($request->input('valor_pago' . $id));

        if ($valorPagamento < $parcela->valor_parcela) {
            return $this->response()->error('Erro. Valor do pagamento é inferior ao valor da parcela!');
        }

        $venda = $parcela->venda()->get();

        $parcela->status = 1;
        $parcela->valor_pago = $valorPagamento;
        $parcela->pagamento = Carbon::now();
        $parcela->save();

        if ($parcela->parcela === $venda[0]->parcelas) {
            $venda = Venda::find($venda[0]->id);
            $venda->status = 1;
            $venda->save();
        }


        return $this->response()->success('Pagamento efetuado com sucesso!' )->refresh();

    }

    public function form(Model $model): void
    {
        $id = $this->row()->getKey();

        Admin::script(
            "$('#valor_pago{$id}').maskMoney({
                  prefix:'R$ ',
                  allowNegative: true,
                  thousands:'.', decimal:',',
                  affixesStay: true});
        ");

        $totalParcela = 'R$ ' . $model->find($id)->valor_parcela;
        $numeroParcela = $model->find($id)->parcela;

        $this->text('parcela'. $id ,'Parcela de número')
            ->attribute(['autocomplete' => 'off'])
            ->value($numeroParcela)
            ->disable();

        $this->text('valor_parcela'. $id ,'Total da parcela')
            ->attribute(['autocomplete' => 'off'])
            ->value($totalParcela)
            ->disable();

        $this->text('valor_pago'. $id ,'Valor à pagar')
            ->attribute(['autocomplete' => 'off'])
            ->required();

    }

    public function display($value)
    {
        return "<i class=\"fa fa-money\"></i>";
    }

}
