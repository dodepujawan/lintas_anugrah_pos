<?php
use App\Models\Menu;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB,Schema};
return new class extends Migration {
 public function up():void { Schema::table('driver_receivables',function(Blueprint $t){$t->string('origin',20)->default('TRANSACTION')->index()->after('number');$t->foreignId('counter_coa_id')->nullable()->change();});
  $m=Menu::updateOrCreate(['route_name'=>'driver-opening-receivables'],['name'=>'Saldo Awal Piutang Sopir','section'=>'Saldo Awal','icon'=>'bi bi-wallet2','sort_order'=>4,'description'=>'Input rincian saldo awal piutang sopir tanpa jurnal.','is_sidebar'=>true]);
  $source=Menu::where('route_name','driver-receivables')->value('id'); if($source) foreach(DB::table('role_menu')->where('menu_id',$source)->get() as $p) DB::table('role_menu')->updateOrInsert(['role_id'=>$p->role_id,'menu_id'=>$m->id],['can_view'=>$p->can_view,'can_create'=>$p->can_create,'can_edit'=>$p->can_edit,'can_delete'=>$p->can_delete,'can_post'=>$p->can_post,'can_void'=>$p->can_void,'can_export'=>$p->can_export,'created_at'=>now(),'updated_at'=>now()]); }
 public function down():void { /* No destructive rollback: keep driver opening receivable data and menu intact. */ }
};
