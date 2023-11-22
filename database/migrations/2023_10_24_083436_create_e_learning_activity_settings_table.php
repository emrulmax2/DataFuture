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
        Schema::create('e_learning_activity_settings', function (Blueprint $table) {
            $table->id();
            $table->enum('category', ['General', 'Assignment Brief', 'Unit Handbook', 'Harvard Referencing', 'Lecture/Topic']);
            $table->string('logo', 145)->nullable();
            $table->tinyInteger('has_week')->nullable()->default(0);
            $table->tinyInteger('active')->nullable()->default(1);
            
            $table->bigInteger('created_by')->unsigned();
            $table->bigInteger('updated_by')->unsigned()->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('e_learning_activity_settings');
    }
};
