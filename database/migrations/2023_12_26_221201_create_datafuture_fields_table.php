<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('datafuture_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('datafuture_field_category_id');
            $table->string('name');
            $table->enum('type', ['date', 'text', 'number'])->default('text');
            $table->text('description')->nullable();

            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('datafuture_field_category_id', 'df_dfci_frn_k')->references('id')->on('datafuture_field_categories')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('datafuture_fields');
    }
};
