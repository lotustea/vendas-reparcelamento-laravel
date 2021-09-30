<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TableReparcelamento extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reparcelamento', function (Blueprint $table) {
            $table->id();
            $table->integer('cliente_id');
            $table->float('valor_total');
            $table->integer('parcelas');
            $table->float('entrada');
            $table->boolean('status');
            $table->json('vendas_abatidas');
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
        Schema::dropIfExists('reparcelamento');
    }
}
