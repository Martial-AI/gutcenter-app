<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('work_schedules', function (Blueprint $table): void {
            $table->id();
            $table->string('name');                          // ex: "Matin", "Après-midi"
            // null = tous les jours, sinon 0=Lun…6=Dim (ISO: Mon=0)
            $table->unsignedTinyInteger('day_of_week')->nullable();
            $table->time('starts_at');
            $table->time('ends_at');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Pre-populate with default requested schedule: Monday through Friday (8h00 - 11h30 & 14h30 - 18h00)
        $now = now();
        $defaults = [];
        for ($day = 0; $day <= 4; $day++) {
            $defaults[] = [
                'name' => 'Matin',
                'day_of_week' => $day,
                'starts_at' => '08:00:00',
                'ends_at' => '11:30:00',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $defaults[] = [
                'name' => 'Après-midi',
                'day_of_week' => $day,
                'starts_at' => '14:30:00',
                'ends_at' => '18:00:00',
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        \Illuminate\Support\Facades\DB::table('work_schedules')->insert($defaults);
    }

    public function down(): void
    {
        Schema::dropIfExists('work_schedules');
    }
};
