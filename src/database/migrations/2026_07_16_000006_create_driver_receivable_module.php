<?php

use App\Models\Menu;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('drivers', function (Blueprint $t) { $t->id(); $t->string('code',30)->unique(); $t->string('name',100); $t->string('phone',30)->nullable(); $t->text('address')->nullable(); $t->boolean('is_active')->default(true)->index(); $t->timestamps(); });
        Schema::create('driver_receivables', function (Blueprint $t) { $t->id(); $t->string('number',40)->unique(); $t->date('date')->index(); $t->date('due_date')->nullable(); $t->foreignId('driver_id')->constrained('drivers'); $t->string('type',40)->index(); $t->string('vehicle_number',30)->nullable(); $t->string('reference',100)->nullable(); $t->decimal('amount',18,2); $t->decimal('paid',18,2)->default(0); $t->decimal('balance',18,2); $t->string('status',20)->default('OPEN')->index(); $t->text('description')->nullable(); $t->foreignId('receivable_coa_id')->constrained('coas'); $t->foreignId('counter_coa_id')->constrained('coas'); $t->foreignId('journal_id')->nullable()->constrained('jurnals'); $t->foreignId('created_by')->nullable()->constrained('users'); $t->timestamps(); });
        Schema::create('driver_receivable_payments', function (Blueprint $t) { $t->id(); $t->string('number',40)->unique(); $t->foreignId('driver_receivable_id')->constrained('driver_receivables'); $t->date('date')->index(); $t->string('method',30); $t->decimal('amount',18,2); $t->foreignId('payment_coa_id')->constrained('coas'); $t->string('reference',100)->nullable(); $t->text('description')->nullable(); $t->foreignId('journal_id')->constrained('jurnals'); $t->string('status',20)->default('POSTED')->index(); $t->timestamp('voided_at')->nullable(); $t->foreignId('void_journal_id')->nullable()->constrained('jurnals'); $t->foreignId('created_by')->nullable()->constrained('users'); $t->timestamps(); });

        $menus = [
            ['route_name'=>'drivers','name'=>'Master Sopir','section'=>'Master','icon'=>'bi bi-person-badge','sort_order'=>20,'description'=>'Master sopir untuk subledger piutang sopir.'],
            ['route_name'=>'driver-receivables','name'=>'Piutang Sopir','section'=>'Transaksi','icon'=>'bi bi-truck','sort_order'=>20,'description'=>'Tanggungan dan saldo piutang sopir.'],
            ['route_name'=>'driver-payments','name'=>'Pelunasan Piutang Sopir','section'=>'Transaksi','icon'=>'bi bi-cash-coin','sort_order'=>21,'description'=>'Pembayaran dan potongan piutang sopir.'],
        ];
        $sourceId = Menu::where('route_name','piutang-berjalan')->value('id');
        foreach ($menus as $data) {
            $menu=Menu::updateOrCreate(['route_name'=>$data['route_name']],$data+['is_sidebar'=>true]);
            if ($sourceId) foreach (DB::table('role_menu')->where('menu_id',$sourceId)->get() as $p) DB::table('role_menu')->updateOrInsert(['role_id'=>$p->role_id,'menu_id'=>$menu->id],['can_view'=>$p->can_view,'can_create'=>$p->can_create,'can_edit'=>$p->can_edit,'can_delete'=>$p->can_delete,'can_post'=>$p->can_post,'can_void'=>$p->can_void,'can_export'=>$p->can_export,'created_at'=>now(),'updated_at'=>now()]);
        }
    }
    public function down(): void { foreach (['drivers','driver-receivables','driver-payments'] as $route) { $m=Menu::where('route_name',$route)->first(); if($m){DB::table('role_menu')->where('menu_id',$m->id)->delete();$m->delete();}} Schema::dropIfExists('driver_receivable_payments'); Schema::dropIfExists('driver_receivables'); Schema::dropIfExists('drivers'); }
};
