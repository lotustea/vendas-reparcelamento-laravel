<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReparcelamentoParcela extends Model
{
    use HasFactory;

    public function reparcelamento()
    {
        return $this->belongsTo(Reparcelamento::class);
    }
}
