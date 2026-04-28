<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('login_logs', function (Blueprint $table) {
            $table->string('platform', 50)->nullable()->after('user_agent');
            $table->string('device', 100)->nullable()->after('platform');
            $table->string('browser', 100)->nullable()->after('device');
            $table->string('country', 100)->nullable()->after('browser');
            $table->string('city', 100)->nullable()->after('country');
            $table->decimal('lat', 10, 7)->nullable()->after('city');
            $table->decimal('lng', 10, 7)->nullable()->after('lat');
        });
    }

    public function down(): void
    {
        Schema::table('login_logs', function (Blueprint $table) {
            $table->dropColumn(['platform', 'device', 'browser', 'country', 'city', 'lat', 'lng']);
        });
    }
};
