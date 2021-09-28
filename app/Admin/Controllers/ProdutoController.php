<?php

namespace App\Admin\Controllers;

use App\Models\Produto;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class ProdutoController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Produto';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Produto());

        $grid->column('id', __('Id'));
        $grid->column('nome', __('Nome'));
        $grid->column('referencia', __('Referencia'));
        $grid->column('preco', __('Preco'));

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
        $show = new Show(Produto::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('nome', __('Nome'));
        $show->field('referencia', __('Referencia'));
        $show->field('preco', __('Preco'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Produto());

        $form->text('nome', __('Nome'));
        $form->text('referencia', __('Referencia'));
        $form->decimal('preco', __('Preco'));

        return $form;
    }
}
