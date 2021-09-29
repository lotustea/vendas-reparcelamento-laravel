<?php

namespace App\Admin\Controllers;

use App\Models\Venda;
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
        $grid->column('parcelas_vendas', 'Parcelas')
            ->expand(function ($model) {
                $parcelas = $model->parcelas()->take(10)->get()->map(function ($parcela) {
                return
                    $parcela
                        ->only([
                            'parcela',
                            'vencimento',
                            'valor_parcela',
                            'valor_pago',
                            'valor_abatido',
                            'valor_extra',
                            'pagamento',
                            'status'
                        ]);
            });
                return new Table([
                    'parcela',
                    'vencimento',
                    'valor_parcela',
                    'valor_pago',
                    'valor_abatido',
                    'valor_extra',
                    'pagamento',
                    'status'
                ],
                    $parcelas->toArray()
                );
        });

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

        $form->number('id_cliente', __('Id cliente'));
        $form->decimal('total', __('Total'));
        $form->decimal('entrada', __('Entrada'));
        $form->number('parcelas', __('Parcelas'));
        $form->date('data_compra', __('Data compra'))->default(date('Y-m-d'));
        $form->switch('status', __('Status'));

        return $form;
    }
}
