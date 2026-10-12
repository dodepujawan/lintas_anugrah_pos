<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class DriverReceivable extends Model {
 protected $fillable=['number','origin','date','due_date','driver_id','type','vehicle_number','reference','amount','paid','balance','status','description','receivable_coa_id','counter_coa_id','journal_id','created_by'];
 protected $casts=['date'=>'date','due_date'=>'date','amount'=>'decimal:2','paid'=>'decimal:2','balance'=>'decimal:2'];
 public function driver(){return $this->belongsTo(Driver::class);} public function journal(){return $this->belongsTo(Jurnal::class);} public function payments(){return $this->hasMany(DriverReceivablePayment::class);}
}
