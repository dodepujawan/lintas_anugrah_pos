<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expedisi extends Model
{
    use HasFactory;

    protected $table = 'expedisis';

    protected $fillable = [
        'NOMUAT', 'TGLMUAT', 'TGLPO', 'NOPO', 'NOSJ', 'NOJALAN', 'tglsj',
        'KET_SJ', 'TGLINVOICE', 'INVOICE', 'CUSTOMER_KODE', 'CUSTOMER',
        'KENDARAAN', 'NAMA_KENDARAAN', 'DRIVER', 'NAMA_DRIVER', 'KET_DRIVER',
        'DRIVER2', 'NAMA_DRIVER2', 'KET_DRIVER2',
        'barang', 'penyimpanan', 'koli', 'catatan',
        'UT', 'KET_UT', 'PESANAN', 'PESANANGB', 'GB', 'rute', 'RUTE2',
        'JUMLAH', 'UNIT', 'HARGA', 'hargaaw', 'JENISHRG', 'VOL', 'type',
        'DISC', 'NDISC', 'NDISCAW', 'TOTAL', 'PPN', 'GRAND',
        'KODEDC', 'DC', 'DCAW', 'BAYAR', 'PIUTANG', 'claim', 'KETCLAIM',
        'TOP', 'TGLJT', 'SIMPAN', 'SUHU', 'kunci', 'KETERANGAN', 'STS', 'JENIS',
        'user', 'USEREDIT', 'USERINV', 'USERKENDARAAN',
        'REFUND', 'PENERIMA', 'BANK', 'NOREK', 'CT',
        'P_PENERIMA', 'P_ALAMAT', 'P_NAMA', 'P_PHONE',
        'READY', 'CLOSSING', 'TGLCLOSSING', 'USERCLOSSING', 'kwt', 'TGLKW',
        'CABANG', 'POTONGAN', 'WILAYAH',
        'REK_BIAYA', 'BIAYA', 'FNO_PRKB', 'FJUR_TRANSB', 'NO_BKK', 'JURNAL',
        'area_id', 'area_name', 'NOJURNAL', 'jurnal_id',
    ];

    protected $casts = [
        'TGLMUAT' => 'date',
        'TGLPO' => 'date',
        'tglsj' => 'date',
        'TGLINVOICE' => 'date',
        'TGLJT' => 'date',
        'TGLCLOSSING' => 'date',
        'TGLKW' => 'date',
        'JENIS' => 'string',
        'type' => 'string',
        'area_id' => 'integer',
        'jurnal_id' => 'integer',
    ];

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'DRIVER', 'KODE');
    }

    public function kendaraan(): BelongsTo
    {
        return $this->belongsTo(Kendaraan::class, 'KENDARAAN', 'KODE');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Mcustomer::class, 'CUSTOMER', 'CUSTOMER');
    }

    public function jurnal(): BelongsTo
    {
        return $this->belongsTo(Jurnal::class, 'NOJURNAL', 'no_jurnal');
    }

    public function arh(): BelongsTo
    {
        return $this->belongsTo(Arh::class, 'INVOICE', 'NOFAKTUR');
    }
}
