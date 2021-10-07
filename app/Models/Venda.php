<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venda extends Model
{
    use HasFactory;

    public function clienteEmAtraso()
    {
        return $this->belongsTo(ClienteEmAtraso::class);
    }

    public function clientes()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function produtos()
    {
        return $this->belongsToMany(Produto::class, 'venda_produto', 'id_venda');
    }

    public function parcelas()
    {
        return $this->hasMany(VendaParcela::class, 'id_venda');
    }
}
