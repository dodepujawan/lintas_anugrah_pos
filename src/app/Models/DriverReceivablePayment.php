<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class DriverReceivablePayment extends Model { protected $fillable=['number','driver_receivable_id','date','method','amount','payment_coa_id','reference','description','journal_id','status','voided_at','void_journal_id','created_by']; protected $casts=['date'=>'date','amount'=>'decimal:2','voided_at'=>'datetime']; public function receivable(){return $this->belongsTo(DriverReceivable::class,'driver_receivable_id');} public function journal(){return $this->belongsTo(Jurnal::class);} }
