<?php

namespace App\Models;

use Encore\Admin\Grid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Cliente extends Model
{
    use HasFactory;

    public function vendas()
    {
        return $this->hasMany(Venda::class, 'id_cliente')
            ->where('status', '=', '0');
    }

}
