<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClienteEmAtraso extends Model
{
    use HasFactory;

    protected $table = "clientes_em_atraso";

    public function vendas()
    {
        return $this->hasMany(Venda::class, 'id_cliente')
            ->where('status', '=', '0');
    }


}
