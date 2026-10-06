<?php

use Database\Seeders\PolicyQuestionBankSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Log;

/**
 * Loads the college's policies and the drafted question bank from
 * database/data/policy-assessment/question-bank.json.
 *
 * Every seeded question lands inactive (origin 'ai_draft') so HR reviews it
 * before anyone is tested on it. The seeder is idempotent — categories match
 * on name, policies on slug, and questions on their seed key (deleted ones
 * included), so only questions a policy has never had are added — and it is
 * safe to run again by hand after this migration, e.g. when the JSON gains
 * new questions or levels.
 *
 * Nothing to undo here: the tables themselves are dropped by the previous
 * migration's down().
 */
return new class extends Migration
{
    public function up(): void
    {
        try {
            (new PolicyQuestionBankSeeder())->run();
        } catch (\Throwable $e) {
            Log::error('Policy question bank seed failed: '.$e->getMessage());
            throw $e;
        }
    }

    public function down(): void
    {
        //
    }
};
