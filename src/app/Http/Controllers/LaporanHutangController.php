<?php

namespace App\Http\Controllers;

use App\Exports\SubledgerExport;
use App\Models\AccountMapping;
use App\Models\Aph;
use App\Models\Bapd;
use App\Models\Baph;
use App\Services\JournalQueryService;
use App\Services\FastTablePdfService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanHutangController extends Controller
{
    public function __construct(
        private readonly JournalQueryService $journalQueryService,
        private readonly FastTablePdfService $fastTablePdf,
    ) {}

    public function index(Request $request)
    {
        $filters = $this->filters($request);
        $report = $this->report($filters);
        $report['invoiceTotals'] = [
            'count' => $report['invoices']->count(),
            'amount' => $report['invoices']->sum('amount'),
            'paid' => $report['invoices']->sum('paid'),
            'balance' => $report['invoices']->sum('balance'),
        ];
        $report['supplierTotals'] = ['count' => $report['suppliers']->count()];
        $report['paymentTotals'] = [
            'count' => $report['payments']->count(),
            'amount' => $report['payments']->sum(fn (Baph $payment): float => (float) $payment->details->sum('NILAI_BAYAR')),
        ];
        $report['invoices'] = $this->paginateCollection($report['invoices'], $request, 'invoice_page', 50);
        $report['suppliers'] = $this->paginateCollection($report['suppliers'], $request, 'supplier_page', 50);
        $report['payments'] = $this->paginateCollection($report['payments'], $request, 'payment_page', 50);

        return view('laporan.hutang', array_merge($filters, $report));
    }

    public function export(Request $request)
    {
        $filters = $this->filters($request); $report = $this->report($filters);
        return Excel::download(new SubledgerExport($report['invoices'], 'Laporan Hutang', 'Supplier', $filters['tanggalPosisi']), 'laporan_hutang_'.$filters['tanggalPosisi'].'.xlsx');
    }

    public function pdf(Request $request)
    {
        $filters = $this->filters($request);
        $report = $this->report($filters);
        $dotMatrix = $request->input('print_format') === 'dot_matrix';
        $rows = $report['invoices']->map(function (array $row): array {
            $status = $row['status'] === 'Jatuh Tempo' ? 'J.Tempo' : ($row['status'] === 'Belum Jatuh Tempo' ? 'Belum JT' : $row['status']);

            return [
                (string) $row['invoice']->NOFAKTUR,
                $row['invoice']->TGLFAKTUR?->format('d/m/Y') ?? '-',
                $row['invoice']->TGLJT?->format('d/m/Y') ?? '-',
                (string) $row['invoice']->supplier_display,
                (string) $row['age_label'],
                $status,
                number_format((float) $row['amount'], 2, ',', '.'),
                number_format((float) $row['paid'], 2, ',', '.'),
                number_format((float) $row['balance'], 2, ',', '.'),
            ];
        })->all();

        return $this->fastTablePdf->stream([
            'title' => 'LAPORAN HUTANG PER FAKTUR',
            'subtitles' => ['Posisi '.date('d/m/Y', strtotime($filters['tanggalPosisi']))],
            'headings' => ['Faktur', 'Tanggal', 'J.Tempo', 'Supplier', 'Umur', 'Status', 'Nilai', 'Terbayar', 'Sisa'],
            'columns' => [65, 45, 45, 136, 45, 45, 61, 61, 61],
            'rows' => $rows,
            'numeric' => [6, 7, 8],
            'bold_columns' => [8],
            'dot_matrix' => $dotMatrix,
            'font_size' => 5.8,
            'filename' => 'laporan_hutang_'.($dotMatrix ? 'dot_matrix_' : '').now()->format('Ymd_His').'.pdf',
        ]);
    }

    private function filters(Request $request): array
    {
        $v = $request->validate(['tanggal_posisi'=>['nullable','date'],'tanggal_awal'=>['nullable','date'],'tanggal_akhir'=>['nullable','date','after_or_equal:tanggal_awal'],'supplier'=>['nullable','string','max:100'],'status'=>['nullable','in:semua,open,lunas,jatuh_tempo'],'search'=>['nullable','string','max:100']]);
        $position = Carbon::parse($v['tanggal_posisi'] ?? now())->format('Y-m-d');
        return ['tanggalPosisi'=>$position,'tanggalAwal'=>Carbon::parse($v['tanggal_awal'] ?? Carbon::parse($position)->startOfYear())->format('Y-m-d'),'tanggalAkhir'=>Carbon::parse($v['tanggal_akhir'] ?? $position)->format('Y-m-d'),'supplier'=>trim((string)($v['supplier']??'')),'status'=>$v['status']??'semua','search'=>trim((string)($v['search']??''))];
    }

    private function report(array $f): array
    {
        $query = Aph::with('supplierMaster')->whereDate('TGLFAKTUR','<=',$f['tanggalPosisi'])->when($f['supplier']!=='',fn($q)=>$q->where('SUPPLIER',$f['supplier']))->when($f['search']!=='',function($q)use($f){$keyword='%'.$f['search'].'%';$q->where(function($filter)use($keyword){$filter->where('NOFAKTUR','like',$keyword)->orWhere('SUPPLIER','like',$keyword)->orWhere('KETERANGAN','like',$keyword)->orWhereHas('supplierMaster',fn($supplier)=>$supplier->where('NAMA','like',$keyword));});});
        $models=$query->orderBy('TGLFAKTUR')->orderBy('NOFAKTUR')->get();
        $supplierOptions=Aph::whereDate('TGLFAKTUR','<=',$f['tanggalPosisi'])->whereNotNull('SUPPLIER')->distinct()->orderBy('SUPPLIER')->pluck('SUPPLIER');
        $settled=$models->isEmpty()?collect():Bapd::join('baph','baph.NOBAYAR','=','bapd.NOBAYAR')->whereIn('bapd.NOFAKTUR',$models->pluck('NOFAKTUR'))->whereDate('baph.TGLBAYAR','<=',$f['tanggalPosisi'])->where(function($q)use($f){$q->where('baph.STATUS','!=','VOID')->orWhereDate('baph.VOIDED_AT','>',$f['tanggalPosisi']);})->selectRaw('bapd.NOFAKTUR, SUM(bapd.NILAI_BAYAR+bapd.DISCOUNT+bapd.RETUR) settled')->groupBy('bapd.NOFAKTUR')->pluck('settled','bapd.NOFAKTUR');
        $asOf=Carbon::parse($f['tanggalPosisi'])->endOfDay();
        $all=$models->map(function(Aph $i)use($settled,$asOf){$amount=(float)$i->HUTANG;$paid=min($amount,(float)($settled[$i->NOFAKTUR]??0));$balance=max(0,$amount-$paid);$days=$i->TGLJT&&$i->TGLJT->lt($asOf)?(int)$i->TGLJT->diffInDays($asOf):0;return ['invoice'=>$i,'amount'=>$amount,'paid'=>$paid,'balance'=>$balance,'days'=>$days,'age_label'=>$balance<=0?'Lunas':($days<=0?'Belum jatuh tempo':($days<=30?'1–30 hari':($days<=60?'31–60 hari':($days<=90?'61–90 hari':'> 90 hari')))),'status'=>$balance<=0?'Lunas':($days>0?'Jatuh Tempo':'Belum Jatuh Tempo')];});
        $invoices=$all->filter(fn($r)=>match($f['status']){'open'=>$r['balance']>0,'lunas'=>$r['balance']<=0,'jatuh_tempo'=>$r['balance']>0&&$r['days']>0,default=>true})->values();
        $open=$all->where('balance','>',0); $aging=collect(['belum_jatuh_tempo'=>0,'1_30'=>0,'31_60'=>0,'61_90'=>0,'lebih_90'=>0]);
        foreach($open as $r){$key=$r['days']<=0?'belum_jatuh_tempo':($r['days']<=30?'1_30':($r['days']<=60?'31_60':($r['days']<=90?'61_90':'lebih_90')));$aging[$key]+=$r['balance'];}
        $suppliers=$all->groupBy(fn($r)=>$r['invoice']->SUPPLIER)->map(fn(Collection $rows,$code)=>['supplier'=>$code,'supplier_name'=>(string)($rows->first()['invoice']->supplier_display??$code),'count'=>$rows->count(),'amount'=>$rows->sum('amount'),'paid'=>$rows->sum('paid'),'balance'=>$rows->sum('balance')])->sortBy('supplier')->values();
        $payments=Baph::with(['details','supplierMaster'])->whereBetween('TGLBAYAR',[$f['tanggalAwal'],$f['tanggalAkhir']])->where(function($q)use($f){$q->where('STATUS','!=','VOID')->orWhereDate('VOIDED_AT','>',$f['tanggalAkhir']);})->when($f['supplier']!=='',fn($q)=>$q->where('SUPPLIER',$f['supplier']))->orderBy('TGLBAYAR')->get();
        $opening=$this->balanceAt(Carbon::parse($f['tanggalAwal'])->subDay()->format('Y-m-d'),$f['supplier']);$periodInvoices=$all->filter(fn($r)=>$r['invoice']->TGLFAKTUR->betweenIncluded(Carbon::parse($f['tanggalAwal']),Carbon::parse($f['tanggalAkhir'])));$paid=$payments->sum(fn($p)=>(float)$p->details->sum('NILAI_BAYAR'));$adjustments=$payments->sum(fn($p)=>(float)$p->details->sum(fn($d)=>(float)$d->DISCOUNT+(float)$d->RETUR));
        $movement=['opening'=>$opening,'invoices'=>$periodInvoices->sum('amount'),'payments'=>$paid,'adjustments'=>$adjustments,'ending'=>$opening+$periodInvoices->sum('amount')-$paid-$adjustments];
        $payableCoaId=AccountMapping::getCoaId('accounts_payable');$ledgerBalance=null;if($payableCoaId&&$f['supplier']===''){$ledger=$this->journalQueryService->getSumByCoaId('1900-01-01',$f['tanggalPosisi'],$payableCoaId);$ledgerBalance=$ledger['kredit']-$ledger['debit'];}$subledgerBalance=$open->sum('balance');
        return compact('invoices','suppliers','supplierOptions','payments','aging','movement','payableCoaId','ledgerBalance','subledgerBalance');
    }

    private function balanceAt(string $date,string $supplier):float
    {
        $invoices=Aph::whereDate('TGLFAKTUR','<=',$date)->when($supplier!=='',fn($q)=>$q->where('SUPPLIER',$supplier))->get();if($invoices->isEmpty())return 0;
        $settled=Bapd::join('baph','baph.NOBAYAR','=','bapd.NOBAYAR')->whereIn('bapd.NOFAKTUR',$invoices->pluck('NOFAKTUR'))->whereDate('baph.TGLBAYAR','<=',$date)->where(function($q)use($date){$q->where('baph.STATUS','!=','VOID')->orWhereDate('baph.VOIDED_AT','>',$date);})->selectRaw('bapd.NOFAKTUR,SUM(bapd.NILAI_BAYAR+bapd.DISCOUNT+bapd.RETUR) settled')->groupBy('bapd.NOFAKTUR')->pluck('settled','bapd.NOFAKTUR');
        return $invoices->sum(fn($i)=>max(0,(float)$i->HUTANG-(float)($settled[$i->NOFAKTUR]??0)));
    }

    private function paginateCollection(Collection $rows, Request $request, string $pageName, int $perPage): LengthAwarePaginator
    {
        $page = LengthAwarePaginator::resolveCurrentPage($pageName);

        return (new LengthAwarePaginator(
            $rows->forPage($page, $perPage)->values(), $rows->count(), $perPage, $page,
            ['path' => $request->url(), 'pageName' => $pageName]
        ))->appends($request->except($pageName));
    }
}
