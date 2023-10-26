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
        Schema::create('permission_template_groups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('permission_template_id');
            $table->string('name', 191);
            $table->enum('R', [0, 1])->default(0);
            $table->enum('W', [0, 1])->default(0);
            $table->enum('D', [0, 1])->default(0);
            
            $table->bigInteger('created_by')->unsigned();
            $table->bigInteger('updated_by')->unsigned()->nullable();
            $table->softDeletes();
            $table->timestamps();
            
            $table->foreign('permission_template_id')->references('id')->on('permission_templates')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('permission_template_groups');
    }
};
