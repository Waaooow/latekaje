<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('recap_logs', function (Blueprint $table) {
            $table->id();
            $table->string('channel', 32);
            $table->string('target')->nullable();
            $table->unsignedInteger('total')->default(0);
            $table->string('status', 16)->default('ok');
            $table->text('response')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recap_logs');
        Schema::dropIfExists('settings');
    }
};
