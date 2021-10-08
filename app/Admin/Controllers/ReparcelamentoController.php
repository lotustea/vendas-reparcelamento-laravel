<?php

namespace App\Admin\Controllers;

use App\Models\ClienteEmAtraso;
use App\Models\Reparcelamento;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Http\Request;

class ReparcelamentoController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Reparcelamento';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Reparcelamento());

        $grid->column('id', __('Id'));
        $grid->column('cliente_id', __('Cliente id'));
        $grid->column('valor_total', __('Valor total'));
        $grid->column('parcelas', __('Parcelas'));
        $grid->column('entrada', __('Entrada'));
        $grid->column('status', __('Status'));
        $grid->column('vendas_abatidas', __('Vendas abatidas'));
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
        $show = new Show(Reparcelamento::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('cliente_id', __('Cliente id'));
        $show->field('valor_total', __('Valor total'));
        $show->field('parcelas', __('Parcelas'));
        $show->field('entrada', __('Entrada'));
        $show->field('status', __('Status'));
        $show->field('vendas_abatidas', __('Vendas abatidas'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Create interface.
     *
     * @param Request $request
     * @param Content $content
     *
     * @return Content
     */
    public function criar(Request $request, $id, Content $content)
    {

        return $content
            ->title($this->title())
            ->description($this->description['create'] ?? trans('admin.create'))
            ->body($this->form($id));
    }
    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form($cliente = null)
    {
        $form = new Form(new Reparcelamento());
        $form->hidden('cliente_id', 'Cliente')->value($cliente);
        $form->decimal('valor_total', __('Valor total'));
        $form->number('parcelas', 'Selecione a quantidade de parcelas');
        $form->decimal('entrada', __('Entrada'));
        $form->switch('status', __('Status'));
        $form->text('vendas_abatidas', __('Vendas abatidas'));

        return $form;
    }
}