<?php
namespace App\Http\Controllers;
use App\Models\Driver;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
class DriverController extends Controller {
 public function index(Request $r){$drivers=Driver::when($r->filled('search'),fn($q)=>$q->where(fn($x)=>$x->where('code','like','%'.$r->search.'%')->orWhere('name','like','%'.$r->search.'%')->orWhere('phone','like','%'.$r->search.'%')))->orderBy('code')->paginate(20)->withQueryString();return view('drivers.index',compact('drivers'));}
 public function create(){return view('drivers.form');}
 public function store(Request $r){$d=Driver::create($this->valid($r));app(AuditLogService::class)->record('create','drivers',$d,'Menambah master sopir '.$d->code);return redirect()->route('drivers.index')->with('success','Master sopir berhasil ditambahkan.');}
 public function edit(Driver $driver){return view('drivers.form',compact('driver'));}
 public function update(Request $r,Driver $driver){$driver->update($this->valid($r,$driver));app(AuditLogService::class)->record('update','drivers',$driver,'Mengubah master sopir '.$driver->code);return redirect()->route('drivers.index')->with('success','Master sopir berhasil diperbarui.');}
 public function destroy(Driver $driver){if($driver->receivables()->exists())return back()->with('error','Sopir sudah memiliki transaksi dan tidak dapat dihapus. Nonaktifkan saja.');$driver->delete();return back()->with('success','Master sopir berhasil dihapus.');}
 private function valid(Request $r,?Driver $d=null):array{$r->merge(['code'=>strtoupper(trim((string)$r->code))]);return $r->validate(['code'=>['required','string','max:30',Rule::unique('drivers')->ignore($d?->id)],'name'=>['required','string','max:100'],'phone'=>['nullable','string','max:30'],'address'=>['nullable','string','max:500'],'is_active'=>['nullable','boolean']])+['is_active'=>$r->boolean('is_active')];}
}
