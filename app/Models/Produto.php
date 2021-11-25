<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{
    use HasFactory;

    public function vendas()
    {
        return $this->belongsToMany(Venda::class, 'venda_produto', 'id_venda', 'id_produto');
    }
}
