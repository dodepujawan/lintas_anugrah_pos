<?php
namespace App\Exports;
use App\Models\Driver;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\{FromCollection,WithHeadings,WithStyles,WithTitle};
use PhpOffice\PhpSpreadsheet\Style\{Alignment,Fill,NumberFormat};
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
class DriverOpeningReceivableExport implements FromCollection,WithHeadings,WithStyles,WithTitle {
 public function __construct(private readonly Collection $rows,private readonly string $search='',private readonly string $driverId=''){}
 public function collection():Collection{$data=$this->rows->map(fn($r)=>[$r->date?->format('d/m/Y'),$r->number,$r->driver?->code.' - '.$r->driver?->name,$r->due_date?->format('d/m/Y'),$r->vehicle_number?:'-',$r->reference?:'-',(float)$r->amount,(float)$r->paid,(float)$r->balance,(float)$r->balance<=0?'Lunas':'Belum Lunas']);$data->push(['','','TOTAL','','','',$this->rows->sum('amount'),$this->rows->sum('paid'),$this->rows->sum('balance'),'']);return $data;}
 public function headings():array{$driver=$this->driverId!==''?Driver::find($this->driverId):null;$filter=collect([$driver?'Sopir: '.$driver->code.' - '.$driver->name:null,$this->search!==''?'Pencarian: '.$this->search:null])->filter()->join(' | ');return [['SALDO AWAL PIUTANG SOPIR'],[$filter?:'Seluruh data saldo awal'],['Tanggal','Nomor','Sopir','Jatuh Tempo','Kendaraan','Referensi','Piutang Awal','Dibayar','Sisa','Status']];}
 public function styles(Worksheet $s):array{$s->mergeCells('A1:J1');$s->mergeCells('A2:J2');$s->freezePane('A4');foreach(range('A','J')as$c)$s->getColumnDimension($c)->setAutoSize(true);$s->getStyle('A1:J2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);$s->getStyle('A1')->getFont()->setBold(true)->setSize(14);$s->getStyle('A3:J3')->applyFromArray(['font'=>['bold'=>true],'fill'=>['fillType'=>Fill::FILL_SOLID,'startColor'=>['rgb'=>'E2E8F0']]]);$last=$s->getHighestRow();if($last>=4)$s->getStyle('G4:I'.$last)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);$s->getStyle('A'.$last.':J'.$last)->getFont()->setBold(true);return[];}
 public function title():string{return'Saldo Awal Sopir';}
}
