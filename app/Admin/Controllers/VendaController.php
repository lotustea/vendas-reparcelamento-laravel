<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\ReparcelamentoParcelaPagarAction;
use App\Admin\Helpers\Methods;
use App\Admin\Selectable\Produtos;
use App\Admin\Tables\ParcelasTable;
use App\Models\Cliente;
use App\Models\Venda;
use Carbon\Carbon;
use Encore\Admin\Admin;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
Use Encore\Admin\Widgets\Table;
use Illuminate\Http\Request;

class VendaController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Venda';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Venda());

        $grid->filter(function($filter){
            $filter->disableIdFilter();
            $filter->where(function ($query) {
                $query->whereHas('cliente', function ($query) {
                    $query->where('nome', 'like', "%{$this->input}%");
                });
            }, 'Cliente');
        });

        $grid->column('id_cliente','Cliente')->display(function ($cliente) {
            return Cliente::find($cliente)->nome;
        });

        $grid->column('data_compra', __('Data compra'))->display(function ($data) {
            return  Carbon::parse($data)->format('d/m/Y') .' - '. Carbon::parse($data)->diffForHumans();
        });

        $grid->column('total', __('Valor total'))->display(function ($valor) {
            return 'R$ ' . Methods::toReal($valor);
        });

        $grid->column('entrada', __('Entrada'))->display(function ($entrada) {
            return 'R$ ' . Methods::toReal($entrada);
        });

        new ParcelasTable($grid->column('parcelas_vendas', 'Parcelas'));

        $grid->column('status', __('Status'))
            ->using([0 => 'Em aberto', 1 => 'Pago']);

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Venda::findOrFail($id));

        $show->id_cliente('Cliente')->as(function ($cliente){
            return Cliente::find($cliente)->nome;
        });
        $show->field('total', __('Total'));
        $show->field('entrada', __('Entrada'));
        $show->field('parcelas', __('Parcelas'));
        $show->field('data_compra', __('Data compra'));
        $show->field('status', __('Status'));

        $hoje = Carbon::now();

        $show->produtos('Produtos', function ($produtos){
            $produtos->resource('/admin/venda-produtos');
            $produtos->disableExport();
            $produtos->disablePagination();
            $produtos->disableFilter();
            $produtos->disableRowSelector();
            $produtos->disableCreateButton();
            $produtos->disableActions();
            $produtos->disableColumnSelector();

            $produtos->id_venda()->display(function ($venda){

            })
            $produtos->nome();

            $produtos->preco()->display(function ($preco) {
                dd($preco);
                return 'R$ ' . Methods::toReal($preco);
            });

        });

        $show->parcelas('Parcelas', function ($parcelas) use ($hoje){
            $parcelas->resource('/admin/reparcelamento-parcelas');
            $parcelas->disableExport();
            $parcelas->disablePagination();
            $parcelas->disableFilter();
            $parcelas->disableRowSelector();
            $parcelas->disableCreateButton();
            $parcelas->disableActions();
            $parcelas->disableColumnSelector();

            $parcelas->parcela();

            $parcelas->valor_parcela()->display(function ($valorParcela) {
                return 'R$ ' . Methods::toReal($valorParcela);
            });

            $parcelas->vencimento()->display(function ($vencimento) use ($hoje){
                if(Carbon::parse($vencimento) <= $hoje){
                    return Carbon::parse($vencimento)->format('d/m/Y - ') . Carbon::parse($vencimento)->diffForHumans() . " - vencido.";
                }
                return Carbon::parse($vencimento)->format('d/m/Y - ') . Carbon::parse($vencimento)->diffForHumans();

            });

            $parcelas->valor_pago()->display(function ($valorPago) {
                return 'R$ ' . Methods::toReal($valorPago);
            });

            $parcelas->status()->using([0 => 'Em aberto', 1 => 'Pago']);

            $parcelas->pagar()->display(function ($title, $column) {
                If ($this->status == 1) {
                    return 'Pago';
                }
                return $column->action(ReparcelamentoParcelaPagarAction::class);
            });

        });


        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Venda());

        $form->setAction('/admin/vendas/criar-venda');

        Admin::js('js/venda.js');

        $form->select('cliente_nome', 'Cliente')
            ->options(function () {
                return Cliente::all()->pluck('nome',"id");
            })
            ->rules('required');

        $form->belongsToMany('produtos', Produtos::class, 'Produtos');

        $form->select('parcelas', 'Quantidade de parcelas')
            ->options([
                1 => '1x', 2 => '2x', 3 => '3x', 4 => '4x', 5 => '5x', 6 => '6x',
                7 => '7x', 8 => '8x', 9 => '9x', 10 => '10x', 11 => '11x', 12 => '12x',
                13 => '13x', 14 => '14x', 15 => '15x', 16 => '16x', 17 => '17x', 18 => '18x',
                19 => '19x', 20 => '20x', 21 => '21x', 22 => '22x', 23 => '23x', 24 => '24x',
            ])
            ->placeholder('Selecione a quantidade de parcelas')
            ->required()
            ->attribute(['autocomplete' => 'off']);

        $form->datetime('primeiro_vencimento', 'Vencimento da primeira parcela')
            ->placeholder('Selecione a data')
            ->format('DD-MM-YY')
            ->required();

        $form->text('valor_compra','Valor Compra')
            ->placeholder('Valor da compra')
            ->attribute(['autocomplete' => 'off'])
            ->required();

        $form->text('valor_entrada', 'Entrada')
            ->placeholder('Digite o valor de entrada, se houver')
            ->attribute(['autocomplete' => 'off']);

        $form->text('valor_total', 'Total')
            ->placeholder('Valor total da compra')
            ->required()
            ->attribute(['autocomplete' => 'off']);

        return $form;
    }

    /**
     * @param Request $request
     */
    public function calcularValorCompra(Request $request)
    {

        return dd(collect($request->input('produtos')));
    }
}
