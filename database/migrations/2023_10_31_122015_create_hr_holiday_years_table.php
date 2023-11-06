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
        Schema::create('hr_holiday_years', function (Blueprint $table) {
            $table->id();
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('notice_period');
            $table->tinyInteger('bf_entitlement')->default(0);
            $table->tinyInteger('active')->default(0);
            
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
        Schema::dropIfExists('hr_holiday_years');
    }
};
