<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ClientesComDividas extends Migration
{
    public function up(): void
    {
        DB::statement(
            "CREATE VIEW clientes_em_atraso AS
                            select clientes.*
                            from clientes
                            inner join vendas on clientes.id = vendas.id_cliente
                            inner join venda_parcelas on vendas.id = venda_parcelas.id_venda
                            where venda_parcelas.vencimento < CURDATE( )
                            and venda_parcelas.status != 1
                            and vendas.status != 1
                            group by clientes.id"
        );
    }
    public function down(): void
    {
        DB::statement("DROP VIEW clientes_em_atraso");
    }
}
