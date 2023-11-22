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
        Schema::create('hr_bank_holidays', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hr_holiday_year_id');
            $table->string('name', 191);
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('duration');
            $table->text('description')->nullable();
            
            $table->bigInteger('created_by')->unsigned();
            $table->bigInteger('updated_by')->unsigned()->nullable();
            $table->softDeletes();
            $table->timestamps();
            
            $table->foreign('hr_holiday_year_id', 'hr_holiday_year_id_frn_key')->references('id')->on('hr_holiday_years')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hr_bank_holidays');
    }
};
