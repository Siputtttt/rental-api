<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;

class Download implements FromCollection, WithHeadings
{
    use Exportable;
    private $header ;
    private $rows ;

    public function __construct(  $args)
    {
        $this->headers = $args['headers'] ;
        $this->rows = $args['rows'] ;

    }
    public function collection()
    {
        return collect($this->rows);
    }

    public function headings(): array
    {
        return  $this->headers ;
    }

}