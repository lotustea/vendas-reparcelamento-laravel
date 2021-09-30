<?php

namespace App\Admin\Controllers;

use App\Models\ReparcelamentoParcela;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ReparcelamentoParcelaController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'ReparcelamentoParcela';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new ReparcelamentoParcela());

        $grid->column('id', __('Id'));
        $grid->column('reparcelamento_id', __('Reparcelamento id'));
        $grid->column('valor_total', __('Valor total'));
        $grid->column('numero_parcela', __('Numero parcela'));
        $grid->column('vencimento', __('Vencimento'));
        $grid->column('status', __('Status'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));

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
        $show = new Show(ReparcelamentoParcela::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('reparcelamento_id', __('Reparcelamento id'));
        $show->field('valor_total', __('Valor total'));
        $show->field('numero_parcela', __('Numero parcela'));
        $show->field('vencimento', __('Vencimento'));
        $show->field('status', __('Status'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new ReparcelamentoParcela());

        $form->number('reparcelamento_id', __('Reparcelamento id'));
        $form->decimal('valor_total', __('Valor total'));
        $form->number('numero_parcela', __('Numero parcela'));
        $form->date('vencimento', __('Vencimento'))->default(date('Y-m-d'));
        $form->switch('status', __('Status'));

        return $form;
    }
}
