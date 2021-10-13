<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reparcelamento extends Model
{
    use HasFactory;

    public function parcelas()
    {
        return $this->hasMany(ReparcelamentoParcela::class, 'reparcelamento_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
}
