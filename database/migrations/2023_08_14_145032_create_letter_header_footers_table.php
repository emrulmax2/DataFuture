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
        Schema::create('letter_header_footers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('path', 191);
            $table->string('current_file_name', 191);
            $table->enum('type', ['Header', 'Footer']);
            $table->enum('for_letter', ['Yes', 'No']);
            $table->enum('for_email', ['Yes', 'No']);
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
        Schema::dropIfExists('letter_header_footers');
    }
};
