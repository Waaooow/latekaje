<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->string('return_pin', 6)->nullable()->after('status');
            $table->string('returned_by')->nullable()->after('return_pin');
            $table->string('return_relation', 16)->default('sendiri')->after('returned_by');
            $table->string('return_method', 16)->default('mandiri')->after('return_relation');
            $table->string('received_by')->nullable()->after('return_method');
            $table->string('return_photo_path')->nullable()->after('received_by');
        });

        foreach (DB::table('loans')->whereNull('return_pin')->get(['id']) as $row) {
            DB::table('loans')->where('id', $row->id)->update([
                'return_pin' => sprintf('%06d', random_int(0, 999999)),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn([
                'return_pin', 'returned_by', 'return_relation',
                'return_method', 'received_by', 'return_photo_path',
            ]);
        });
    }
};
