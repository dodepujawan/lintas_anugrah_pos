<?php
namespace App\Exports;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\{FromCollection,ShouldAutoSize,WithHeadings,WithMapping,WithStyles,WithTitle};
use PhpOffice\PhpSpreadsheet\Style\{Alignment,Fill,NumberFormat};
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
class DriverReceivableReportExport implements FromCollection,WithHeadings,WithMapping,WithStyles,ShouldAutoSize,WithTitle {
 public function __construct(private Collection $rows){}
 public function collection(){return $this->rows;}
 public function headings():array{return ['Tanggal','Nomor','Kode Sopir','Nama Sopir','Asal','Jenis','Kendaraan','Jatuh Tempo','Nilai','Dibayar','Saldo','Status','Keterangan'];}
 public function map($r):array{return [$r->date?->format('d/m/Y'),$r->number,$r->driver?->code,$r->driver?->name,$r->origin==='OPENING'?'Saldo Awal':'Berjalan',str_replace('_',' ',$r->type),$r->vehicle_number,$r->due_date?->format('d/m/Y'),(float)$r->amount,(float)$r->paid,(float)$r->balance,$r->status,$r->description];}
 public function title():string{return 'Piutang Sopir';}
 public function styles(Worksheet $sheet):array{$sheet->getStyle('A1:M1')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');$sheet->getStyle('A1:M1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF1E3A5F');$sheet->getStyle('I:K')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);$sheet->getStyle('I:K')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);$sheet->setAutoFilter($sheet->calculateWorksheetDimension());$sheet->freezePane('A2');return [];}
}
