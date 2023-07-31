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
        Schema::create('comon_smtps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('smtp_user', 191);
            $table->string('smtp_pass', 80);
            $table->string('smtp_host', 191);
            $table->string('smtp_port', 45);
            $table->string('smtp_encryption', 45);
            $table->string('smtp_authentication', 45);
            $table->tinyInteger('account_type')->default('0');

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
        Schema::dropIfExists('comon_smtps');
    }
};
