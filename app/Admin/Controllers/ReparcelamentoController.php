<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\ClienteNegociarDividaAction;
use App\Admin\Actions\ReparcelamentoParcelaPagarAction;
use App\Admin\Forms\ReparcelarForm;
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
        $grid->disableActions();
        $grid->disableColumnSelector();
        $grid->disableBatchActions();
        $grid->disableCreateButton();

        $grid->filter(function($filter){
            $filter->disableIdFilter();
            $filter->where(function ($query) {
                $query->whereHas('cliente', function ($query) {
                    $query->where('nome', 'like', "%{$this->input}%");
                });
            }, 'Cliente');
        });

        $grid->column('id', __('Id'));

        $grid->column('ver', 'Ver')->display(
            function () {
                $id = $this->getKey();
                return  "
                                <a
                                    href='reparcelamentos/{$id}'
                                    class='btn btn-sm btn-default'
                                    title='Ver'
                                >
                                <i class='fa fa-eye'></i>
                                </a>";
            }
        );
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
        $show->panel()
            ->tools(function ($tools) {
                $tools->disableEdit();
                $tools->disableList();
                $tools->disableDelete();
            });

        $show->field('id', __('Id'));
        $show->cliente_id('Cliente')->as(function ($cliente){
            return Cliente::find($cliente)->nome;
        });
        $show->valor_total()->as(function ($valor){
            return 'R$ ' . Methods::toReal($valor);
        });
        $show->entrada()->as(function ($entrada){
            return 'R$ ' . Methods::toReal($entrada);
        });
        $show->field('parcelas', __('Parcelas'));
        $show->status()->using([0 => 'Em aberto', 1 => 'Pago']);
        $show->created_at('Criado em')->as(function ($data){
            return Carbon::parse($data)->format('d/m/Y - ') . Carbon::parse($data)->diffForHumans();
        });

        $hoje = Carbon::now();
        $show->parcelas('Parcelas', function ($parcelas) use ($hoje){
            $parcelas->resource('/admin/reparcelamento-parcelas');
            $parcelas->disableExport();
            $parcelas->disablePagination();
            $parcelas->disableFilter();
            $parcelas->disableRowSelector();
            $parcelas->disableCreateButton();
            $parcelas->disableActions();
            $parcelas->disableColumnSelector();

            $parcelas->valor_total()->display(function ($valorTotal) {
                return 'R$ ' . Methods::toReal($valorTotal);
            });

            $parcelas->numero_parcela();

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
                // Otherwise it is displayed as editable
                return $column->action(ReparcelamentoParcelaPagarAction::class);
            });

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

    public function reparcelar($id)
    {
        $cliente = Cliente::find($id);
        $totalEmdividas = $cliente->totalEmDividas($cliente, true);
        $content =  (new Content())
            ->title('Reparcelamento de dívidas')
            ->body(new ReparcelarForm(
                ['id' => $id, 'valor_negociado' => $totalEmdividas, 'valor_total'  => $totalEmdividas]
            ));
        if ($result = session('success')) {
            $content->row('<pre>'.json_encode($result).'</pre>');
        }
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
