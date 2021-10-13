<?php

namespace App\Admin\Controllers;

use App\Admin\Helpers\Methods;
use App\Admin\Tables\VendasAbatidasTable;
use App\Models\Cliente;
use App\Models\ClienteEmAtraso;
use App\Models\Reparcelamento;
use Carbon\Carbon;
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
    protected $title = 'Renegociações';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Reparcelamento());

        $grid->column('id', __('Id'));
        $grid->column('cliente_id','Cliente')->display(function ($cliente) {
            return Cliente::find($cliente)->nome;
        });
        $grid->column('created_at', __('Criado em'))->display(function () {
            return  Carbon::parse($this->created_at)->format('d/m/Y  H:i:s - ') . $this->created_at->diffForHumans();
        });
        $grid->column('valor_total', __('Valor total'))->display(function ($valor) {
            return 'R$ ' . Methods::toReal($valor);
        });
        $grid->column('entrada', __('Entrada'))->display(function ($entrada) {
            return 'R$ ' . Methods::toReal($entrada);
        });;
        $grid->column('status', __('Status'))
            ->using([0 => 'Em aberto', 1 => 'Pago']);

        new VendasAbatidasTable($grid->column('', 'Vendas abatidas'));


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
        $show->cliente_id()->as(function ($cliente){
            return Cliente::find($cliente)->nome;
        });
        $show->field('valor_total', __('Valor total'));
        $show->field('parcelas', __('Parcelas'));
        $show->field('entrada', __('Entrada'));
        $show->status()->using([0 => 'Em aberto', 1 => 'Pago']);
        //$show->field('vendas_abatidas', __('Vendas abatidas'));
        $show->field('created_at', __('Criado em'));
        //$show->field('updated_at', __('Updated at'));
        $show->parcelas('Parcelas', function ($parcelas){
            $parcelas->resource('/admin/reparcelamento-parcelas');
            $parcelas->disableExport();
            $parcelas->disablePagination();
            $parcelas->disableFilter();
            $parcelas->disableRowSelector();
            $parcelas->disableCreateButton();

            $parcelas->valor_total();
            $parcelas->numero_parcela();
            $parcelas->vencimento();
            $parcelas->valor_pago();
            $parcelas->status();

        });

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
