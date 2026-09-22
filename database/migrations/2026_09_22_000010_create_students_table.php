<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('nis')->nullable()->unique();
            $table->string('nama');
            $table->string('kelas');
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });

        Schema::table('loans', function (Blueprint $table) {
            $table->string('nis')->nullable()->after('nama_siswa');
            $table->foreignId('student_id')->nullable()->after('nis')
                ->constrained('students')->nullOnDelete();
        });
    }

    public function down(): void
    {
        $prefix = (string) config('database.connections.mysql.prefix', '');
        $fk = $prefix.'loans_student_id_foreign';

        $exists = (int) \Illuminate\Support\Facades\DB::selectOne(
            'SELECT COUNT(*) AS c FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
             WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = ? AND CONSTRAINT_NAME = ?',
            [$prefix.'loans', $fk]
        )->c;

        Schema::table('loans', function (Blueprint $table) use ($fk, $exists) {
            if ($exists) {
                $table->dropForeign($fk);
            }
            $table->dropColumn(['nis', 'student_id']);
        });
    }
};
