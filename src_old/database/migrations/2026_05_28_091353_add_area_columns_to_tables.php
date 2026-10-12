<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // =========================
        // USERS
        // =========================
        Schema::table('users', function (Blueprint $table) {

            $table->unsignedBigInteger('area_id')
                ->nullable()
                ->after('role_old');

            $table->string('area_name')
                ->nullable()
                ->after('area_id');
        });

        // =========================
        // EXPEDISI
        // =========================
        Schema::table('expedisi', function (Blueprint $table) {

            $table->unsignedBigInteger('area_id')
                ->nullable()
                ->after('user');

            $table->string('area_name')
                ->nullable()
                ->after('area_id');
        });

        // =========================
        // COOLROOMS
        // =========================
        Schema::table('coolrooms', function (Blueprint $table) {

            $table->unsignedBigInteger('area_id')
                ->nullable()
                ->after('USEREDIT');

            $table->string('area_name')
                ->nullable()
                ->after('area_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         // =========================
        // USERS
        // =========================
        Schema::table('users', function (Blueprint $table) {

            $table->dropColumn([
                'area_id',
                'area_name'
            ]);
        });

        // =========================
        // EXPEDISI
        // =========================
        Schema::table('expedisi', function (Blueprint $table) {

            $table->dropColumn([
                'area_id',
                'area_name'
            ]);
        });

        // =========================
        // COOLROOMS
        // =========================
        Schema::table('coolrooms', function (Blueprint $table) {

            $table->dropColumn([
                'area_id',
                'area_name'
            ]);
        });
    }
};
