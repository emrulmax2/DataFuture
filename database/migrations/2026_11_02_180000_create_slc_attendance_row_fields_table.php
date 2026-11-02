<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SLC bulk attendance › Data check (user, 2 Nov 2026): what was decided about each field of an
 * attendance line that differs from our records (update / coc / contact / accept).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('slc_attendance_row_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('slc_attendance_import_row_id')->constrained('slc_attendance_import_rows')->cascadeOnDelete();
            $table->string('field', 20);
            $table->enum('route', ['coc', 'contact', 'accept']);
            $table->unsignedBigInteger('slc_coc_id')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->unique(['slc_attendance_import_row_id', 'field'], 'slc_att_row_field_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slc_attendance_row_fields');
    }
};
