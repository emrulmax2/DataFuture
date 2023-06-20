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
        Schema::create('permission_templates', function (Blueprint $table) {
            $table->id();
            $table->index('permission_category_id');
            $table->bigInteger('permission_category_id')->unsigned();
            $table->index('role_id');
            $table->bigInteger('role_id')->unsigned();
            $table->index('department_id');
            $table->bigInteger('department_id')->unsigned();
            $table->string('type',191);
            $table->enum('R', ['0', '1'])->default('0');
            $table->enum('W', ['0', '1'])->default('0');
            $table->enum('D', ['0', '1'])->default('0');
            $table->bigInteger('created_by');
            $table->bigInteger('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->foreign('permission_category_id')->references('id')->on('permission_categories')->onDelete('cascade');
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $table->dropForeign(['permission_category_id']);
        $table->dropIndex('lists_permission_category_id_index');
        $table->dropColumn('permission_category_id');
        $table->dropForeign(['role_id']);
        $table->dropIndex('lists_role_id_index');
        $table->dropColumn('role_id');
        $table->dropForeign(['department_id']);
        $table->dropIndex('lists_department_id_index');
        $table->dropColumn('department_id');
        Schema::dropIfExists('permission_templates');
    }
};
