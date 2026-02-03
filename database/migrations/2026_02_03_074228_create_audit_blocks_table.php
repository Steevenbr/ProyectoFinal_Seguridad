<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('audit_blocks', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('sequence')->unique(); // 1,2,3...
            $table->unsignedBigInteger('user_id')->nullable();

            $table->string('event_type', 60); // role_changed, user_deleted, etc
            $table->json('event_data');       // datos del evento (sin secretos)

            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();

            $table->char('prev_hash', 64);
            $table->char('hash', 64)->unique();

            $table->timestamps();

            $table->index(['user_id', 'event_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_blocks');
    }
};
