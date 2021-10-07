<?php

namespace App\Admin\Actions;

use Encore\Admin\Actions\RowAction;
use Illuminate\Database\Eloquent\Model;


class ClienteNegociarDividaAction extends RowAction
{
    public $name = 'Renegociar Dívidas';

    public function handle(Model $model)
    {
        // $model ...

        return $this->response()->success('Success message.')->refresh();
    }

    public function form()
    {
        $this->checkbox('type', 'type')->options([]);
        $this->textarea('reason', 'reason')->rules('required');
    }

    public function html()
    {
        return "<a class='report-posts btn btn-sm btn-danger'><i class='fa fa-info-circle'></i>Report</a>";
    }
}
