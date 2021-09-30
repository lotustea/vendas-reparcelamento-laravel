<?php

namespace App\Admin\Helpers;

class Methods
{

    public function toReal($price)
    {
        return number_format($price, 2, ',', '.');
    }

    public function toFloat($price)
    {
        $price = str_replace('R$ ', '', $price);
        $price = str_replace('.', '', $price);
        $price = str_replace(',', '.', $price);
        return $price;
    }

    public function newDate()
    {
        return date('d-m-Y H:m:s');
    }

    public function newDateDb()
    {
        return date('Y-m-d H:m:s');
    }


}
