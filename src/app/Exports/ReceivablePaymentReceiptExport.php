<?php
namespace App\Exports;
use App\Models\{Barh,Bard};
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\{FromCollection,ShouldAutoSize,WithHeadings,WithStyles,WithTitle};
use PhpOffice\PhpSpreadsheet\Style\{Alignment,Border,Fill,NumberFormat};
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
class ReceivablePaymentReceiptExport implements FromCollection,WithHeadings,WithStyles,ShouldAutoSize,WithTitle {
 public function __construct(private Barh $payment,private Collection $details){}
 public function collection(){return $this->details->map(fn(Bard $d)=>[$d->NOFAKTUR,$d->TGLFAKTUR?->format('d/m/Y'),(float)$d->SALDO_FAKTUR,(float)$d->NILAI_BAYAR,(float)$d->PPH23,(float)$d->DISCOUNT,(float)$d->BANK_ADMIN,(float)$d->RETUR,(float)$d->SISA_FAKTUR]);}
 public function headings():array{return [['BUKTI PELUNASAN PIUTANG'],['No. Bukti: '.$this->payment->NOBAYAR],['Tanggal: '.$this->payment->TGLBAYAR?->format('d/m/Y').' | Pelanggan: '.$this->payment->CUSTOMER.' - '.($this->payment->CUSTOMER_NAME??'')],['Metode: '.$this->payment->METODE_PEMBAYAN.' | Referensi: '.($this->payment->NO_REFERENSI??'-').' | Jurnal: '.($this->payment->NOJURNAL??'-')],[],['No. Faktur','Tanggal Faktur','Saldo Faktur','Bayar','PPh 23','Potongan','Adm. Bank','Retur','Sisa Faktur']];}
 public function styles(Worksheet $s):array{foreach([1,2,3,4]as$r)$s->mergeCells("A{$r}:I{$r}");$s->getStyle('A1:I1')->getFont()->setBold(true)->setSize(15);$s->getStyle('A1:I4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);$s->getStyle('A6:I6')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');$s->getStyle('A6:I6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF1E3A5F');$last=$s->getHighestRow();$s->getStyle("A6:I{$last}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);$s->getStyle("C7:I{$last}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);$s->freezePane('A7');return [];}
 public function title():string{return 'Bukti '.$this->payment->NOBAYAR;}
}
