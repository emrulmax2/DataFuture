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
        Schema::create('acc_banks', function (Blueprint $table) {
            $table->id();
            $table->string('bank_name', 199);
            $table->string('bank_image', 199)->nullable();
            $table->enum('status', [1, 2])->default(1);
            $table->enum('audit_status', [1, 0])->nullable(0);
            $table->double('opening_balance', 10, 2)->default(0);
            $table->date('opening_date')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
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
        Schema::dropIfExists('acc_banks');
    }
};
