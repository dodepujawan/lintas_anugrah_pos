<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void { foreach(['barh','baph'] as $table) Schema::table($table,function(Blueprint $t){$t->string('STATUS',20)->default('ACTIVE')->index();$t->text('VOID_REASON')->nullable();$t->timestamp('VOIDED_AT')->nullable();$t->foreignId('VOIDED_BY')->nullable()->constrained('users')->nullOnDelete();$t->string('NOJURNAL_PEMBALIK',50)->nullable();}); }
    public function down(): void { foreach(['barh','baph'] as $table) Schema::table($table,function(Blueprint $t){$t->dropConstrainedForeignId('VOIDED_BY');$t->dropColumn(['STATUS','VOID_REASON','VOIDED_AT','NOJURNAL_PEMBALIK']);}); }
};
