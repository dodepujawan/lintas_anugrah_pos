<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
return new class extends Migration {public function up():void{DB::table('menus')->where('route_name','penjualan-hosting')->update(['name'=>'Sinkronisasi Penjualan','description'=>'Validasi dan proses sinkronisasi data penjualan dari hosting.','updated_at'=>now()]);DB::table('menus')->where('route_name','import-data')->update(['name'=>'Impor Piutang & Hutang','description'=>'Impor data piutang dan hutang dari file Excel.','updated_at'=>now()]);}public function down():void{DB::table('menus')->where('route_name','penjualan-hosting')->update(['name'=>'Proses Penjualan Hosting','updated_at'=>now()]);DB::table('menus')->where('route_name','import-data')->update(['name'=>'Import Data','updated_at'=>now()]);}};
