<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TableReparcelamentoParcelas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reparcelamento_parcelas', function (Blueprint $table) {
            $table->id();
            $table->integer('reparcelamento_id');
            $table->float('valor_total');
            $table->integer('numero_parcela');
            $table->date('vencimento');
            $table->date('data_pagamento')->nullable();
            $table->boolean('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reparcelamento_parcelas');
    }
}
