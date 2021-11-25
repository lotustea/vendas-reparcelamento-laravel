<?php

namespace App\Admin\Forms;

use App\Admin\Helpers\Methods;
use App\Models\Cliente;
use App\Models\Reparcelamento;
use App\Models\ReparcelamentoParcela;
use App\Models\Venda;
use App\Models\VendaParcela;
use Carbon\Carbon;
use Encore\Admin\Admin;
use Encore\Admin\Widgets\Form;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReparcelarForm extends Form
{
    public $name = 'Renegociar Dívidas';

    public function handle(Request $request)
    {

    }



    public function form(): void
    {
        $id = $this->data['id'];
        $cliente = Cliente::find($id);
        $totalEmdividas = $this->data['valor_negociado'];

        Admin::script(
            "$('#valor_negociado').maskMoney({
                  prefix:'R$ ',
                  allowNegative: true,
                  thousands:'.', decimal:',',
                  affixesStay: true});
            $('#valor_entrada').maskMoney({
                  prefix:'R$ ',
                  allowNegative: true,
                  thousands:'.', decimal:',',
                  affixesStay: true
            });
            $('#valor_total').maskMoney({
                  prefix:'R$ ',
                  allowNegative: true,
                  thousands:'.', decimal:',',
                  affixesStay: true
            });
        ");

        $this->radio('cliente', 'Cliente',)
            ->options([
                $id => $cliente->nome . ' - Valor total devido: ' . $totalEmdividas
            ])
            ->default($id)
            ->rules('required')->disable();

        $this->datetime('primeiro_vencimento', 'Vencimento da primeira parcela')
            ->placeholder('Selecione a data')
            ->format('DD-MM-YY')
            ->rules('required');

        $this->select('parcelas', 'Quantidade de parcelas')
            ->options([
                1 => '1x', 2 => '2x', 3 => '3x', 4 => '4x', 5 => '5x', 6 => '6x',
                7 => '7x', 8 => '8x', 9 => '9x', 10 => '10x', 11 => '11x', 12 => '12x',
                13 => '13x', 14 => '14x', 15 => '15x', 16 => '16x', 17 => '17x', 18 => '18x',
                19 => '19x', 20 => '20x', 21 => '21x', 22 => '22x', 23 => '23x', 24 => '24x',
            ])
            ->placeholder('Selecione a quantidade de parcelas')
            ->rules('required')
            ->attribute(['autocomplete' => 'off']);

        $this->text('valor_negociado','Valor Negociação')
            ->placeholder('Digite o valor negociado com o cliente')
            ->attribute(['autocomplete' => 'off'])
            ->rules('required');

        $this->text('valor_entrada', 'Entrada')
            ->placeholder('Digite o valor de entrada, se houver')
            ->attribute(['autocomplete' => 'off']);

        $this->text('valor_total', 'Total')
            ->placeholder('Valor total da negociação')
            ->rules('required')
            ->attribute(['autocomplete' => 'off']);
    }

}
