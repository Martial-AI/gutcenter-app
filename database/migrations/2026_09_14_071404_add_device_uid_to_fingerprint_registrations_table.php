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
        Schema::table('fingerprint_registrations', function (Blueprint $table) {
            $table->unsignedInteger('device_uid')
                ->nullable()
                ->after('finger_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fingerprint_registrations', function (Blueprint $table) {
            $table->dropColumn('device_uid');
        });
    }
};
