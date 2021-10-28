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
use Encore\Admin\Admin;
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
    public function criar($id, Content $content)
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
    protected function form($cliente)
    {
        $form = new Form(new Reparcelamento());
        $form->setAction('/admin/reparcelamentos/efetuar-negociacao');

        $cliente = Cliente::find($cliente);
        $totalEmdividas = $cliente->totalEmDividas($cliente, true);


        $form->radio('cliente_nome', 'Cliente')
            ->options([
                $cliente->id => $cliente->nome . ' - Valor total devido: ' . $totalEmdividas
            ])
            ->default($cliente->id)
            ->rules('required')->disable();

        $form->hidden('cliente', 'Cliente')->value($cliente->id);

        $form->datetime('primeiro_vencimento', 'Vencimento da primeira parcela')
            ->placeholder('Selecione a data')
            ->format('DD-MM-YY')
            ->required();

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

        $form->text('valor_negociado','Valor Negociação')
            ->placeholder('Digite o valor negociado com o cliente')
            ->attribute(['autocomplete' => 'off'])
            ->required();

        $form->text('valor_entrada', 'Entrada')
            ->placeholder('Digite o valor de entrada, se houver')
            ->attribute(['autocomplete' => 'off']);

        $form->text('valor_total', 'Total')
            ->placeholder('Valor total da negociação')
            ->required()
            ->attribute(['autocomplete' => 'off']);

        return $form;
    }
}
