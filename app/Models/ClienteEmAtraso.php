<?php

namespace App\Models;

use App\Admin\Helpers\Methods;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClienteEmAtraso extends Model
{
    use HasFactory;

    protected $table = "clientes_em_atraso";

    public function vendas()
    {
        return $this->hasMany(Venda::class, 'id_cliente')->with('parcelas')
            ->where('vendas.status', '!=', '1');
    }

    /**
     * Calcula e retorna o total em compras do cliente
     * Primeiro parametro é o id do cliente, segundo parametro bool
     * retorno formatado para real brasileiro (default false)
     *
     * @param int $id
     * @param bool $formatacao
     * @return string
     */
    public static function totalEmCompras($id, $formatacao = false)
    {
        $total = self::find($id)->vendas()->sum('total');

        return $formatacao ? 'R$' . Methods::toReal($total) : $total;
    }

    /**
     * Calcula e retorna o valor total pago pelo cliente
     * Primeiro parametro é o id do cliente, segundo parametro bool
     * retorno formatado para real brasileiro (default false)
     *
     * @param int $id
     * @param bool $formatacao
     * @return string
     */
    public static function totalPago($id, $formatacao = false)
    {
        $vendas = self::find($id)->vendas;
        $total = 0;

        foreach ($vendas as $venda) {
           $total += $venda->parcelas()->sum('valor_pago');
        }

        return $formatacao ? 'R$' . Methods::toReal($total) : $total;
    }

    /**
     * Calcula e retorna o valor total devido pelo cliente
     * Primeiro parametro é o id do cliente, segundo parametro bool
     * retorno formatado para real brasileiro (default false)
     *
     * @param int $id
     * @param bool $formatacao
     * @return string
     */
    public static function totalDevido($id, $formatacao = false)
    {
        $total = self::totalEmCompras($id) - self::totalPago($id);

        return $formatacao ? 'R$' . Methods::toReal($total) : $total;
    }

    /**
     * Calcula e retorna o valor total devido de todos os clientes
     * primeiro parametro bool retorno formatado para real brasileiro (default false)
     *
     * @param bool $formatacao
     * @return string
     */
    public static function totalDividendos($formatacao = false)
    {
        $total = 0;

        foreach (self::all() as $cliente) {
            $total += self::totalDevido($cliente->id);
        }

        return $formatacao ? 'R$' . Methods::toReal($total) : $total;
    }
}
