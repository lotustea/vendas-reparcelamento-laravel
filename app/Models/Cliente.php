<?php

namespace App\Models;

use App\Admin\Helpers\Methods;
use Encore\Admin\Grid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Cliente extends Model
{
    use HasFactory;

    public function vendas()
    {
        return $this->hasMany(Venda::class, 'id_cliente')
            ->where('status', '!=', '1');
    }

    public function totalEmDividas(Cliente $cliente, $formatacao = false){
        $total = self::totalEmParcelas($cliente) - self::totalPago($cliente);

        return $formatacao ? 'R$' . Methods::toReal($total) : $total;
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
     * Calcula e retorna o total em parcelas do cliente
     * Primeiro parametro é o id do cliente, segundo parametro bool
     * retorno formatado para real brasileiro (default false)
     *
     * @param int $id
     * @param bool $formatacao
     * @return string
     */
    public static function totalEmParcelas(Cliente $cliente, $formatacao = false)
    {
        $vendas = $cliente->vendas()->get();
        $total = 0;

        foreach ($vendas as $venda) {
            $total += $venda->parcelasEmAberto()->sum('valor_parcela') + $venda->parcelasEmAberto()->sum('valor_extra');
        }

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
    public static function totalPago(Cliente $cliente, $formatacao = false)
    {
        $vendas = $cliente->vendas()->get();
        $total = 0;

        foreach ($vendas as $venda) {
            $total += $venda->parcelasEmAberto()->sum('valor_pago');
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
    public static function totalDevido(Cliente $cliente, $formatacao = false)
    {
        $total = self::totalEmParcelas($cliente) - self::totalPago($cliente);

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
            $total += self::totalDevido($cliente);
        }

        return $formatacao ? 'R$' . Methods::toReal($total) : $total;
    }
}
