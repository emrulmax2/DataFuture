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
        Schema::create('document_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name',191);
            $table->enum('type', ['optional', 'mandatory'])->default('optional');
            $table->enum('application', ['0', '1'])->default('0');
            $table->enum('admission', ['0', '1'])->default('0');
            $table->enum('registration', ['0', '1'])->default('0');
            $table->enum('live', ['0', '1'])->default('0');
            $table->enum('student_profile', ['0', '1'])->default('0');
            $table->bigInteger('created_by');
            $table->bigInteger('updated_by')->nullable();
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
        
        Schema::dropIfExists('document_settings');
    }
};
