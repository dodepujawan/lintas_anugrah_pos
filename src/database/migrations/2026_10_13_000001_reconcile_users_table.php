<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'user_id')) {
                $table->string('user_id')->nullable()->after('id');
            }

            if (! Schema::hasColumn('users', 'role_old')) {
                $table->string('role_old')->default('staff')->after('password');
            }

            if (! Schema::hasColumn('users', 'area_id')) {
                $table->unsignedBigInteger('area_id')->nullable()->after('role_old');
            }

            if (! Schema::hasColumn('users', 'area_name')) {
                $table->string('area_name')->nullable()->after('area_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('users', 'user_id')) $columns[] = 'user_id';
            if (Schema::hasColumn('users', 'role_old')) $columns[] = 'role_old';
            if (Schema::hasColumn('users', 'area_id')) $columns[] = 'area_id';
            if (Schema::hasColumn('users', 'area_name')) $columns[] = 'area_name';

            if (! empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
