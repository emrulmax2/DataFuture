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
        Schema::create('previous_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 199);
            $table->tinyInteger('is_hesa')->default(0);
            $table->string('hesa_code', 99)->nullable();
            $table->tinyInteger('is_df')->default(0);
            $table->string('df_code', 99)->nullable();
            $table->tinyInteger('active')->default(0);

            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('previous_providers');
    }
};
