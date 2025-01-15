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
        Schema::create('agent_bank_details', function (Blueprint $table) {
            $table->id();
            $table->index('agent_id');
            $table->bigInteger('agent_id')->unsigned();
            
            $table->string('beneficiary');
            $table->string('sort_code', 191);
            $table->string('ac_no', 191);
            $table->smallInteger('active')->default(0);

            $table->bigInteger('created_by');
            $table->bigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('agent_id')->references('id')->on('agents')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_bank_details');
    }
};
