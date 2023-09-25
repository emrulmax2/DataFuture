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
        Schema::table('student_contacts', function (Blueprint $table) {
            $table->dropColumn('address_line_1');
            $table->dropColumn('address_line_2');
            $table->dropColumn('state');
            $table->dropColumn('post_code');
            $table->dropColumn('city');
            $table->string('personal_email',191)->nullable()->after("mobile_verification");
            $table->tinyInteger('personal_email_verification')->default('0')->nullable()->after("mobile_verification");
            
            $table->string('term_time_post_code',191)->nullable()->after("permanent_post_code");   
            $table->string('term_time_accommodation_type',191)->nullable()->after("home");
            $table->bigInteger('term_time_address_id')->unsigned()->nullable()->after("permanent_country_id");
            $table->bigInteger('permanent_address_id')->unsigned()->nullable()->after("permanent_country_id");
            $table->foreign('term_time_address_id')->references('id')->on('addresses')->onDelete('set null')->onUpdate('set null');
            $table->foreign('permanent_address_id')->references('id')->on('addresses')->onDelete('set null')->onUpdate('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('student_contacts', function (Blueprint $table) {
            $table->string('address_line_1',191);
            $table->string('address_line_2',191)->nullable();
            $table->string('state',145)->nullable();
            $table->string('post_code',145);
            $table->string('city',145);
            $table->dropForeign(['term_time_address_id']);
            $table->dropColumn(['term_time_address_id']);
            $table->dropForeign(['permanent_address_id']);
            $table->dropColumn(['permanent_address_id']);
            $table->dropColumn('personal_email');
            $table->dropColumn('personal_email_verification');
            $table->dropColumn('term_time_accommodation_type');
            $table->dropColumn('term_time_post_code');
        });
    }
};
