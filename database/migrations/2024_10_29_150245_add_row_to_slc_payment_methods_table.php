<?php

use App\Models\SlcPaymentMethod;
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
        $data = array(
            array('id' => 12, 'name' => 'Online Transfer', 'created_by' => 1)
        );
        
        SlcPaymentMethod::insert($data);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('slc_payment_methods', function (Blueprint $table) {
            //
        });
    }
};
