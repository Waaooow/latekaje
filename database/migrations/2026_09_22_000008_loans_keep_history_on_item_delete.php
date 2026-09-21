<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Normalisasi relasi loans → asset_items:
     * - active_item_id STORED → VIRTUAL (syarat MariaDB agar FK boleh ON DELETE SET NULL)
     * - FK nullOnDelete: hapus unit tidak menghilangkan riwayat pinjaman.
     */
    public function up(): void
    {
        $prefix = (string) config('database.connections.mysql.prefix', '');
        $table = $prefix.'loans';
        $db = (string) DB::selectOne('SELECT DATABASE() AS d')->d;

        $keyNames = collect(DB::select("SHOW INDEX FROM `{$table}`"))->pluck('Key_name')->all();

        Schema::table('loans', function ($blueprint) use ($keyNames) {
            if (in_array('v3_loans_active_item_id_foreign', $keyNames, true)) {
                $blueprint->dropIndex('v3_loans_active_item_id_foreign');
            }

            if (in_array('v3_loans_active_item_id_unique', $keyNames, true)) {
                $blueprint->dropUnique('v3_loans_active_item_id_unique');
            }
        });

        // MariaDB tidak mengizinkan MODIFY STORED→VIRTUAL langsung (1907),
        // jadi DROP + ADD ulang (nilai generated dihitung ulang otomatis).
        $cols = collect(DB::select("SHOW COLUMNS FROM `{$table}`"))->pluck('Field')->all();

        if (in_array('active_item_id', $cols, true)) {
            DB::statement("ALTER TABLE `{$table}` DROP COLUMN `active_item_id`");
        }

        DB::statement(
            "ALTER TABLE `{$table}` ADD COLUMN `active_item_id` BIGINT UNSIGNED
             GENERATED ALWAYS AS (IF(`status` = 'aktif', `asset_item_id`, NULL)) VIRTUAL"
        );
        DB::statement("ALTER TABLE `{$table}` ADD UNIQUE KEY `v3_loans_active_item_id_unique` (`active_item_id`)");

        $fkExists = (int) DB::selectOne(
            'SELECT COUNT(*) AS c FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
             WHERE CONSTRAINT_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME = ?',
            [$db, $table, 'fk_v3_loans_item']
        )->c;

        if (! $fkExists) {
            DB::statement(
                "ALTER TABLE `{$table}` ADD CONSTRAINT `fk_v3_loans_item`
                 FOREIGN KEY (`asset_item_id`) REFERENCES `{$prefix}asset_items` (`id`) ON DELETE SET NULL"
            );
        }
    }

    public function down(): void
    {
        $prefix = (string) config('database.connections.mysql.prefix', '');
        $table = $prefix.'loans';

        DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `fk_v3_loans_item`");
    }
};
