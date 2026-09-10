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
        Schema::create('fingerprint_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type'); // 'student' or 'user'
            $table->unsignedBigInteger('entity_id');
            $table->string('identifier')->unique(); // e.g. ST26-0001 or PA26-001
            $table->string('name')->nullable();
            $table->unsignedTinyInteger('finger_index')->default(0);
            $table->string('device_ip')->nullable()->default('192.168.0.201');
            $table->foreignId('enrolled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('enrolled_at')->nullable();
            $table->timestamps();

            $table->index(['entity_type', 'entity_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fingerprint_registrations');
    }
};
