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
        Schema::create('user_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('device_id');
            $table->string('device_type', 10); // android | ios
            $table->unsignedBigInteger('token_id')->nullable(); // personal_access_tokens.id (current active token)
            $table->string('last_ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('last_logout_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'device_id']);
            $table->index('device_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_devices');
    }
};
