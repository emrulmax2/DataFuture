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
        Schema::create('applicant_view_unlocks', function (Blueprint $table) {
            $table->id();
            $table->index('user_id');
            $table->bigInteger('user_id')->nullable()->unsigned();
            $table->index('applicant_id');
            $table->bigInteger('applicant_id')->unsigned();
            $table->string('token',50);
            $table->timestamp('expired_at');
            $table->bigInteger('created_by');
            $table->bigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('user_id')->nullable()->references('id')->on('users')->onDelete('set null');
            $table->foreign('applicant_id')->references('id')->on('applicants')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $table->dropForeign(['user_id']);
        $table->dropIndex('lists_user_id_index');
        $table->dropForeign(['applicant_id']);
        $table->dropIndex('lists_applicant_id_index');
        Schema::dropIfExists('applicant_view_unlocks');
    }
};
