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
        Schema::table('course_base_datafutures', function (Blueprint $table) {
            $table->dropColumn(['field_name', 'field_type', 'field_desc']);

            $table->unsignedBigInteger('datafuture_field_id')->after('course_id');

            $table->foreign('datafuture_field_id')->references('id')->on('datafuture_fields')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('course_base_datafutures', function (Blueprint $table) {
            $table->dropForeign('datafuture_field_id');
            $table->dropColumn('datafuture_field_id');
            
            $table->string('field_name', 191)->nullable()->after('course_id');
            $table->enum('field_type', ['date', 'text', 'number'])->nullable()->after('field_name');
            $table->text('field_desc')->nullable()->after('field_type');
        });
    }
};
