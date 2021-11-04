<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\ClienteNegociarDividaAction;
use App\Admin\Helpers\Methods;
use App\Admin\Tables\VendasTable;
use App\Models\Cliente;
use App\Models\ClienteEmAtraso;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Box;
Use Encore\Admin\Admin;
use Illuminate\Support\Facades\Request;
use function GuzzleHttp\Promise\all;

class ClienteEmAtrasoController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Cliente em atraso';

    private $methods;

    private ClienteEmAtraso $modelo;

    public function __construct()
    {
        $this->methods = new Methods();
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new ClienteEmAtraso());
        $grid->disableActions();
        $grid->disableCreateButton();

        $grid->filter(function($filter){
            $filter->disableIdFilter();
            $filter->like('nome', 'Nome');
            //$filter->
        });

        /*$grid->header(function () {

            $view = view('admin.table.partials.total-a-receber');

            return new Box('Total a Receber', $view);
        });*/

        $grid->column('nome', __('Nome'));
        $grid->column('cpf', __('Cpf'));
        $grid->column('email', __('Email'));
        $grid->column('telefone', __('Telefone'));
        $grid->column('estado', __('Estado'));
        $grid->column('cidade', __('Cidade'));
        $grid->column('indicacao', __('Indicacao'));
        new VendasTable($grid->column('vendas_cliente', 'Vendas em aberto'));
        $grid->column('dividas', 'Total em dívidas')->display(
            function () {
                $id = $this->getKey();
                $cliente = Cliente::find($id);
                return $cliente->totalEmDividas($cliente, true);
            }
        )->label('danger');
        $grid->column('reparcelar', 'Reparcelar dívidas')->display(
            function () {
                $id = $this->getKey();
                return  "
                                <a
                                    href='reparcelamentos/criar/cliente/{$id}'
                                    class='btn btn-sm btn-default'
                                    title='Reparcelar'
                                >
                                <i class='fa fa-money'></i>
                                </a>";
            }
        );

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
        $show = new Show(Cliente::findOrFail($id));

        $show->field('nome', __('Nome'))
            ->setWidth(5, 5);

        $show->field('cpf', __('Cpf'))
            ->setWidth(5, 5);

        $show->field('email', __('Email'))
            ->setWidth(5, 5);

        $show->field('telefone', __('Telefone'))
            ->setWidth(5, 5);

        $show->field('telefone2', __('Telefone2'))
            ->setWidth(5, 5);

        $show->field('cep', __('Cep'))
            ->setWidth(5, 5);

        $show->field('estado', __('Estado'))
            ->setWidth(5, 5);

        $show->field('cidade', __('Cidade'))
            ->setWidth(5, 5);

        $show->field('bairro', __('Bairro'))
            ->setWidth(5, 5);

        $show->field('rua', __('Rua'))
            ->setWidth(5, 5);

        $show->field('numero', __('Numero'))
            ->setWidth(5, 5);

        $show->field('complemento_comercial', __('Complemento comercial'))
            ->setWidth(5, 5);

        $show->field('indicacao', __('Indicacao'))
            ->setWidth(5, 5);

        $show->field('data_nascimento', __('Data nascimento'))
            ->setWidth(5, 5);

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Cliente());

        $form->text('nome', __('Nome'));
        $form->text('cpf', __('Cpf'));
        $form->email('email', __('Email'));
        $form->text('telefone', __('Telefone'));
        $form->text('telefone2', __('Telefone2'));
        $form->text('cep', __('Cep'));
        $form->text('estado', __('Estado'));
        $form->text('cidade', __('Cidade'));
        $form->text('bairro', __('Bairro'));
        $form->text('rua', __('Rua'));
        $form->text('numero', __('Numero'));
        $form->text('complemento', __('Complemento'));
        $form->text('cep_comercial', __('Cep comercial'));
        $form->text('estado_comercial', __('Estado comercial'));
        $form->text('cidade_comercial', __('Cidade comercial'));
        $form->text('bairro_comercial', __('Bairro comercial'));
        $form->text('rua_comercial', __('Rua comercial'));
        $form->text('numero_comercial', __('Numero comercial'));
        $form->text('complemento_comercial', __('Complemento comercial'));
        $form->text('indicacao', __('Indicacao'));
        $form->date('data_nascimento', __('Data nascimento'))->default(date('Y-m-d'));
        $form->text('senha', __('Senha'));

        return $form;
    }

    public function totalDividendos()
    {
        return ClienteEmAtraso::totalDividendos();
    }
}
