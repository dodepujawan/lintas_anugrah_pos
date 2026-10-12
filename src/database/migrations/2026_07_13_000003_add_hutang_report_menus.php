<?php
use App\Models\Menu;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
return new class extends Migration {
    public function up(): void {
        $source=Menu::where('route_name','hutang')->first();
        foreach ([
            ['route_name'=>'saldo-awal-hutang','name'=>'Input Saldo Awal Hutang','section'=>'Transaksi','icon'=>'bi bi-wallet2','sort_order'=>6,'is_sidebar'=>true],
            ['route_name'=>'laporan.hutang','name'=>'Laporan Hutang','section'=>'Laporan','icon'=>'bi bi-file-earmark-bar-graph','sort_order'=>9,'is_sidebar'=>true],
        ] as $data) {
            $menu=Menu::updateOrCreate(['route_name'=>$data['route_name']],$data);
            if($source) foreach(DB::table('role_menu')->where('menu_id',$source->id)->get() as $p) DB::table('role_menu')->updateOrInsert(['role_id'=>$p->role_id,'menu_id'=>$menu->id],[
                'can_view'=>$p->can_view,'can_create'=>$data['route_name']==='saldo-awal-hutang'?$p->can_create:false,'can_edit'=>false,'can_delete'=>false,'can_post'=>false,'can_void'=>false,'can_export'=>$data['route_name']==='laporan.hutang'?$p->can_export:false,'created_at'=>now(),'updated_at'=>now(),
            ]);
        }
        Menu::where('route_name','hutang')->update(['name'=>'Input Hutang Berjalan','sort_order'=>7]);
        Menu::where('route_name','pelunasan-hutang')->update(['sort_order'=>8]);
    }
    public function down(): void { foreach(['saldo-awal-hutang','laporan.hutang'] as $route){$m=Menu::where('route_name',$route)->first();if($m){DB::table('role_menu')->where('menu_id',$m->id)->delete();$m->delete();}} }
};
