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
        Schema::create('plan_contents', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('category');
            $table->string('logo');
            $table->tinyInteger('is_mandatory')->default(0);
            $table->unsignedBigInteger('plans_date_list_id');
            $table->unsignedBigInteger('e_learning_activity_setting_id')->nullable();
            $table->timestamp('availibility_at',$precision = 0);
            $table->integer('days_reminder')->default(0);
            $table->bigInteger('created_by');
            $table->bigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('plans_date_list_id')->references('id')->on('plans_date_lists')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('e_learning_activity_setting_id','learning_content_foreign')->references('id')->on('e_learning_activity_settings')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('plan_contents');
    }
};
