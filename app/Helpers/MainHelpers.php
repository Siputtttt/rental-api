<?php
// app/Helpers/FormatHelper.php
namespace App\Helpers;
 

class MainHelpers
{
    public static function tanggal($tanggal)
    {
        return Carbon::parse($tanggal)->format('d-m-Y');
    }
    public static function currency( $value  ) {
        return number_format( $value );
    }
}