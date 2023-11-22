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
        Schema::create('plan_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category');
            $table->string('description');
            $table->string('logo');
            $table->unsignedBigInteger('plan_id');
            $table->unsignedBigInteger('e_learning_activity_setting_id')->nullable();
            $table->tinyInteger('has_week')->default(0);
            $table->tinyInteger('is_mandatory')->default(1);
            $table->integer('days_reminder')->default(0);
            $table->bigInteger('created_by');
            $table->bigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('plan_id')->references('id')->on('plans')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('e_learning_activity_setting_id', 'learning_activity_id_foreign')->references('id')->on('e_learning_activity_settings')->onDelete('set null')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('plan_tasks');
    }
};
