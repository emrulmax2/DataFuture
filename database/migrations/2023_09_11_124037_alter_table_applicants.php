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
        Schema::table('applicants', function (Blueprint $table) {
            $table->enum('proof_type', ['passport','birth','driving','nid','respermit'])->nullable()->after('country_id');;
            $table->string('proof_id', 100)->nullable()->after(('proof_type'));
            $table->date('proof_expiredate')->nullable()->after(('proof_id'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('applicants', function (Blueprint $table) {
            $table->dropColumn('proof_type');
            $table->dropColumn('proof_id');
            $table->dropColumn('proof_expiredate');
        });
    }
};
