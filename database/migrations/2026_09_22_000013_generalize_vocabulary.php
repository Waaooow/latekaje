<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Generalisasi kosakata: students→members, school_classes→groups,
     * roles -> superadmin/admin/staff/assistant/users.
     */
    public function up(): void
    {
        $prefix = (string) config('database.connections.mysql.prefix', '');
        $t = fn (string $table): string => $prefix.$table;

        // 1. Lepas FK yang menunjuk ke tabel/kolom yang akan di-rename.
        $this->dropForeignIfExists($t('loans'), 'student_id');
        $this->dropForeignIfExists($t('users'), 'student_id');

        // 2. Rename tabel.
        DB::statement('ALTER TABLE `'.$t('students').'` RENAME TO `'.$t('members').'`');
        DB::statement('ALTER TABLE `'.$t('school_classes').'` RENAME TO `'.$t('groups').'`');

        // 3. Rename kolom.
        DB::statement('ALTER TABLE `'.$t('members').'` RENAME COLUMN `nis` TO `code`');
        DB::statement('ALTER TABLE `'.$t('members').'` RENAME COLUMN `nama` TO `name`');
        DB::statement('ALTER TABLE `'.$t('members').'` RENAME COLUMN `kelas` TO `group`');
        DB::statement('ALTER TABLE `'.$t('users').'` RENAME COLUMN `nis` TO `code`');
        DB::statement('ALTER TABLE `'.$t('users').'` RENAME COLUMN `student_id` TO `member_id`');
        DB::statement('ALTER TABLE `'.$t('loans').'` RENAME COLUMN `student_id` TO `member_id`');
        DB::statement('ALTER TABLE `'.$t('loans').'` RENAME COLUMN `nis` TO `code`');
        DB::statement('ALTER TABLE `'.$t('loans').'` RENAME COLUMN `nama_siswa` TO `borrower_name`');
        DB::statement('ALTER TABLE `'.$t('loans').'` RENAME COLUMN `kelas` TO `group`');

        // 4. Pasang kembali FK (nullOnDelete, seperti semula).
        Schema::table('loans', function (Blueprint $table) {
            $table->foreign('member_id')->references('id')->on('members')->nullOnDelete();
        });
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('member_id')->references('id')->on('members')->nullOnDelete();
        });

        // 5. Migrasi data role lama -> baru.
        DB::table($t('users'))->where('role', 'toolman')->update(['role' => 'admin']);
        DB::table($t('users'))->where('role', 'anak_pkl')->update(['role' => 'assistant']);
        DB::table($t('users'))->where('role', 'siswa')->update(['role' => 'users']);

        // 6. Email sintetis akun member mengikuti domain baru.
        DB::table($t('users'))->where('email', 'like', '%@siswa.latekaje')
            ->update(['email' => DB::raw("REPLACE(email, '@siswa.latekaje', '@member.latekaje')")]);
    }

    public function down(): void
    {
        $prefix = (string) config('database.connections.mysql.prefix', '');
        $t = fn (string $table): string => $prefix.$table;

        $this->dropForeignIfExists($t('loans'), 'member_id');
        $this->dropForeignIfExists($t('users'), 'member_id');

        DB::statement('ALTER TABLE `'.$t('loans').'` RENAME COLUMN `member_id` TO `student_id`');
        DB::statement('ALTER TABLE `'.$t('loans').'` RENAME COLUMN `code` TO `nis`');
        DB::statement('ALTER TABLE `'.$t('loans').'` RENAME COLUMN `borrower_name` TO `nama_siswa`');
        DB::statement('ALTER TABLE `'.$t('loans').'` RENAME COLUMN `group` TO `kelas`');
        DB::statement('ALTER TABLE `'.$t('users').'` RENAME COLUMN `code` TO `nis`');
        DB::statement('ALTER TABLE `'.$t('users').'` RENAME COLUMN `member_id` TO `student_id`');
        DB::statement('ALTER TABLE `'.$t('members').'` RENAME COLUMN `code` TO `nis`');
        DB::statement('ALTER TABLE `'.$t('members').'` RENAME COLUMN `name` TO `nama`');
        DB::statement('ALTER TABLE `'.$t('members').'` RENAME COLUMN `group` TO `kelas`');
        DB::statement('ALTER TABLE `'.$t('members').'` RENAME TO `'.$t('students').'`');
        DB::statement('ALTER TABLE `'.$t('groups').'` RENAME TO `'.$t('school_classes').'`');

        Schema::table('loans', function (Blueprint $table) {
            $table->foreign('student_id')->references('id')->on('students')->nullOnDelete();
        });
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('student_id')->references('id')->on('students')->nullOnDelete();
        });

        DB::table($t('users'))->where('role', 'admin')->update(['role' => 'toolman']);
        DB::table($t('users'))->where('role', 'assistant')->update(['role' => 'anak_pkl']);
        DB::table($t('users'))->where('role', 'users')->update(['role' => 'siswa']);
        DB::table($t('users'))->where('email', 'like', '%@member.latekaje')
            ->update(['email' => DB::raw("REPLACE(email, '@member.latekaje', '@siswa.latekaje')")]);
    }
    private function dropForeignIfExists(string $table, string $column): void
    {
        $constraints = DB::select(
            "SELECT CONSTRAINT_NAME AS name FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?
             AND REFERENCED_TABLE_NAME IS NOT NULL",
            [$table, $column]
        );

        foreach ($constraints as $constraint) {
            DB::statement('ALTER TABLE `'.$table.'` DROP FOREIGN KEY `'.$constraint->name.'`');
        }
    }
};

