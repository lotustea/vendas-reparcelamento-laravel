<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendaProduto extends Model
{
    use HasFactory;

    protected $table = "venda_produto";

    public function venda()
    {
        return $this->belongsTo(Venda::class);
    }
    public function produto()
    {
        return $this->belongsToMany(Produto::class, 'produtos', 'id_produto', '');
    }
}
