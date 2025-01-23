<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('agent_comissions', function (Blueprint $table) {
            $table->dropForeign('agent_comissions_student_id_foreign');
            $table->dropForeign('agent_comissions_slc_money_receipt_id_foreign');
            $table->dropColumn(['student_id', 'slc_money_receipt_id', 'receipt_amount', 'comission', 'paid_date', 'paid_amount']);

            $table->unsignedBigInteger('agent_id')->after('id')->nullable();
            $table->unsignedBigInteger('agent_user_id')->after('agent_id');
            $table->unsignedBigInteger('semester_id')->after('agent_comission_rule_id');
            $table->date('entry_date')->after('remittance_ref');

            $table->foreign('agent_id')->references('id')->on('agents')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('agent_user_id')->references('id')->on('agent_users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('semester_id')->references('id')->on('semesters')->onDelete('set null')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agent_comissions', function (Blueprint $table) {
            //
        });
    }
};
