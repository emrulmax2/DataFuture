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
        Schema::create('agent_documents', function (Blueprint $table) {
            $table->id();
            $table->index('agent_id');
            $table->bigInteger('agent_id')->unsigned();
            $table->index('document_setting_id');
            $table->bigInteger('document_setting_id')->unsigned()->nullable();
            $table->tinyInteger('hard_copy_check')->nullable();
            $table->string('doc_type', 145)->nullable();
            $table->string('disk_type', 145)->nullable();
            $table->string('path', 191);
            $table->string('display_file_name', 191);
            $table->string('current_file_name', 191);
            $table->unsignedTinyInteger('type')->default(1)->comment('1=Regular Deocuments, 2=Communication Documents');
            $table->bigInteger('created_by');
            $table->bigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('agent_id')->references('id')->on('agents')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('document_setting_id')->references('id')->on('document_settings')->onDelete('set null')->onUpdate('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_documents');
    }


};
