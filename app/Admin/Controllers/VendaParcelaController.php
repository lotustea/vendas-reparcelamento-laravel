<?php

namespace App\Admin\Controllers;

use App\Models\VendaParcela;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class VendaParcelaController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'VendaParcela';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new VendaParcela());

        $grid->column('id', __('Id'));
        $grid->column('id_venda', __('Id venda'));
        $grid->column('parcela', __('Parcela'));
        $grid->column('vencimento', __('Vencimento'));
        $grid->column('pagamento', __('Pagamento'));
        $grid->column('valor_parcela', __('Valor parcela'));
        $grid->column('valor_pago', __('Valor pago'));
        $grid->column('valor_abatido', __('Valor abatido'));
        $grid->column('valor_extra', __('Valor extra'));
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
        $show = new Show(VendaParcela::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('id_venda', __('Id venda'));
        $show->field('parcela', __('Parcela'));
        $show->field('vencimento', __('Vencimento'));
        $show->field('pagamento', __('Pagamento'));
        $show->field('valor_parcela', __('Valor parcela'));
        $show->field('valor_pago', __('Valor pago'));
        $show->field('valor_abatido', __('Valor abatido'));
        $show->field('valor_extra', __('Valor extra'));
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
        $form = new Form(new VendaParcela());

        $form->number('id_venda', __('Id venda'));
        $form->number('parcela', __('Parcela'));
        $form->date('vencimento', __('Vencimento'))->default(date('Y-m-d'));
        $form->datetime('pagamento', __('Pagamento'))->default(date('Y-m-d H:i:s'));
        $form->decimal('valor_parcela', __('Valor parcela'));
        $form->decimal('valor_pago', __('Valor pago'));
        $form->decimal('valor_abatido', __('Valor abatido'));
        $form->decimal('valor_extra', __('Valor extra'));
        $form->switch('status', __('Status'));

        return $form;
    }
}
