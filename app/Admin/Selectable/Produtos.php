<?php

namespace App\Admin\Selectable;

use App\Admin\Helpers\Methods;
use App\Models\Produto;
use Encore\Admin\Grid\Filter;
use Encore\Admin\Grid\Selectable;

class Produtos extends Selectable
{
    public $model = Produto::class;

    public function make()
    {
        $this->column('referencia');
        $this->column('nome');
        $this->column('preco')->display(function ($preco) {
            return 'R$ ' . Methods::toReal($preco);
        });
        $this->filter(function (Filter $filter) {
            $filter->disableIdFilter();
            $filter->like('nome');
            $filter->like('referencia');
        });
    }

}
