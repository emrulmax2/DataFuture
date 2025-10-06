<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Enums\EsignEventType;

return new class extends Migration
{
    public function up(): void
    {
        $values = implode("','", array_map(
            fn (EsignEventType $type) => $type->value,
            EsignEventType::cases()
        ));

        DB::statement("ALTER TABLE applicant_e_signature_events MODIFY event_type ENUM('$values') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE applicant_e_signature_events 
            MODIFY event_type ENUM(
                'Sign Request Created',
                'Email Sent',
                'Email Read',
                'Viewed',
                'Location Verified',
                'Consented to Esign',
                'Finalized',
                'Modified',
                'Sign Request Finalized',
                'Renamed'
            ) NOT NULL");
    }
};
