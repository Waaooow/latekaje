<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nis')->nullable()->unique()->after('email');
            $table->foreignId('student_id')->nullable()->after('nis')
                ->constrained('students')->nullOnDelete();
        });
    }

    public function down(): void
    {
        $prefix = (string) config('database.connections.mysql.prefix', '');
        $fk = $prefix.'users_student_id_foreign';

        $exists = 0;

        try {
            $exists = (int) DB::selectOne(
                'SELECT COUNT(*) AS c FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
                 WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = ? AND CONSTRAINT_NAME = ?',
                [$prefix.'users', $fk]
            )->c;
        } catch (\Throwable) {
        }

        Schema::table('users', function (Blueprint $table) use ($fk, $exists) {
            if ($exists) {
                $table->dropForeign($fk);
            }
            $table->dropColumn(['nis', 'student_id']);
        });
    }
};
