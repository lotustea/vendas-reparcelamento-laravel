<?php

namespace App\Admin\Controllers;

use App\Admin\Tables\VendasTable;
use App\Models\Cliente;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Http\Request;

class ClienteController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Cliente';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Cliente());

        $grid->filter(function($filter){
            $filter->disableIdFilter();
            $filter->like('nome', 'Nome');
        });
        $grid->column('id', __('Id'));
        $grid->column('nome', __('Nome'));
        $grid->column('cpf', __('Cpf'));
        $grid->column('email', __('Email'));
        $grid->column('telefone', __('Telefone'));
        $grid->column('estado', __('Estado'));
        $grid->column('cidade', __('Cidade'));
        $grid->column('indicacao', __('Indicacao'));
        $grid->column('reparcelar', 'Reparcelar dívidas')->display(
            function () {
                $id = $this->getKey();
                return  "
                                <a
                                    href='/admin/reparcelamentos/criar/cliente/{$id}'
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

        $show->field('id', __('Id'));
        $show->field('nome', __('Nome'));
        $show->field('cpf', __('Cpf'));
        $show->field('email', __('Email'));
        $show->field('telefone', __('Telefone'));
        $show->field('telefone2', __('Telefone2'));
        $show->field('cep', __('Cep'));
        $show->field('estado', __('Estado'));
        $show->field('cidade', __('Cidade'));
        $show->field('bairro', __('Bairro'));
        $show->field('rua', __('Rua'));
        $show->field('numero', __('Numero'));
        $show->field('complemento', __('Complemento'));
        $show->field('cep_comercial', __('Cep comercial'));
        $show->field('estado_comercial', __('Estado comercial'));
        $show->field('cidade_comercial', __('Cidade comercial'));
        $show->field('bairro_comercial', __('Bairro comercial'));
        $show->field('rua_comercial', __('Rua comercial'));
        $show->field('numero_comercial', __('Numero comercial'));
        $show->field('complemento_comercial', __('Complemento comercial'));
        $show->field('indicacao', __('Indicacao'));
        $show->field('data_nascimento', __('Data nascimento'));

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
    public function clientes(Request $request)
    {
        $q = $request->get('q');

        return Cliente::where('nome', 'like', "%$q%")->paginate(null, ['id', 'nome as text']);
    }

}
