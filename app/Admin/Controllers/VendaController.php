<?php

namespace App\Admin\Controllers;

use App\Admin\Tables\ParcelasTable;
use App\Models\Cliente;
use App\Models\Venda;
use Encore\Admin\Admin;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
Use Encore\Admin\Widgets\Table;

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

        $grid->column('id', __('Id'));
        $grid->column('id_cliente', __('Id cliente'));
        $grid->column('total', __('Total'));
        $grid->column('entrada', __('Entrada'));
        $grid->column('parcelas_vendas', 'Parcelas');
        new ParcelasTable($grid->column('parcelas_vendas', 'Parcelas'));
        $grid->column('data_compra', __('Data compra'));
        $grid->column('status', __('Status'));

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

        $show->field('id', __('Id'));
        $show->field('id_cliente', __('Id cliente'));
        $show->field('total', __('Total'));
        $show->field('entrada', __('Entrada'));
        $show->field('parcelas', __('Parcelas'));
        $show->field('data_compra', __('Data compra'));
        $show->field('status', __('Status'));

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
                $clientes = Cliente::all();
                $options = array();
               foreach ($clientes as $cliente) {
                    $options[$cliente->id] = $cliente->nome;
                }
                return $options;
            })->ajax('/admin/api/clientes')
            ->rules('required');

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
}
