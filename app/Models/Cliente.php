<?php

namespace App\Models;

use Encore\Admin\Grid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Cliente extends Model
{
    use HasFactory;

    public function gerarGrid (){
        $grid = new Grid(new Cliente());
        $grid->model()
                ->join('vendas', 'clientes.id', '=', 'vendas.id_cliente')
                ->select('clientes.*')
                ->where('vendas.statsus', '=', '0')
                ->groupBy('clientes.id')
                ->paginate(20)
                ->get();
        return $grid;
    }

    public function vendas()
    {
        return $this->hasMany(Venda::class, 'id_cliente')
            ->where('status', '=', '0');
    }

}
