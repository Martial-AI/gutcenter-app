<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            // 'permanent' = staff with fixed work schedule
            // 'non_permanent' = staff with per-course schedule (e.g. teachers)
            $table->string('employment_type', 20)->default('permanent')->after('contract_type');
        });

        try {
            $profRoleIds = \Illuminate\Support\Facades\DB::table('roles')->where('name', 'Prof')->pluck('id');
            if ($profRoleIds->isNotEmpty()) {
                $teacherUserIds = \Illuminate\Support\Facades\DB::table('model_has_roles')
                    ->where('model_type', \App\Models\User::class)
                    ->whereIn('role_id', $profRoleIds)
                    ->pluck('model_id');
                \Illuminate\Support\Facades\DB::table('users')
                    ->whereIn('id', $teacherUserIds)
                    ->update(['employment_type' => 'non_permanent']);
            }
        } catch (\Throwable $e) {
            // If roles table not loaded or migration executed in isolation
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('employment_type');
        });
    }
};
