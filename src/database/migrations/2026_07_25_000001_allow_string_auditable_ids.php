<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            // Some audited legacy masters use codes (for example LS-01) as
            // their primary key, so the polymorphic ID cannot be an integer.
            $table->string('auditable_id', 191)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('auditable_id')->nullable()->change();
        });
    }
};
