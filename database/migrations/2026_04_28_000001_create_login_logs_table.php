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
        Schema::create('login_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('actor_id');
            $table->string('actor_type', 50);   // 'user' | 'student_user'
            $table->string('guard_name', 30);   // 'web' | 'student'
            $table->string('session_id', 191)->nullable();
            $table->timestamp('login_at');
            $table->timestamp('logout_at')->nullable();
            $table->string('logout_reason', 50)->nullable(); // manual_logout | session_timeout | session_invalidated
            $table->string('ip_address', 51)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['actor_id', 'actor_type', 'logout_at'], 'login_logs_actor_open');
            $table->index('session_id', 'login_logs_session');
            $table->index('login_at', 'login_logs_login_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('login_logs');
    }
};
