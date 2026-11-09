<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Time staff spend on an applicant's record: one row per page visit (a
 * section of the admission portal), opened when the page loads, kept alive by
 * a heartbeat while the tab is visible and closed when the page is left.
 * Raw data for a later report; nothing reads it yet.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applicant_view_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('applicant_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('section', 40)->index();          // details, process, uploads, notes, communication, esign, esign_request, log
            $table->string('route_name', 120)->nullable();
            $table->string('url', 500)->nullable();
            $table->dateTime('started_at');
            $table->dateTime('last_seen_at');                  // last heartbeat; the end when no explicit end arrived
            $table->dateTime('ended_at')->nullable();
            $table->unsignedInteger('active_seconds')->default(0); // visible-tab time, summed from heartbeats
            $table->unsignedSmallInteger('heartbeats')->default(0);
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamps();
            $table->index(['user_id', 'started_at']);
            $table->index(['applicant_id', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applicant_view_sessions');
    }
};
