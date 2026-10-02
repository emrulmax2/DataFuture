<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Policy Assessments: timed tests, and the level of each drawn question.
 *
 * Catch-up for databases that ran 2026_09_30_100001 before it gained these —
 * a fresh install gets them from 100001 itself, and then this does nothing.
 * Every step is guarded and only ever adds, so existing rows keep their data:
 * existing policies stay untimed, existing attempts read as untimed.
 *
 *   policy_documents.time_limit_minutes      minutes allowed per attempt (NULL = untimed)
 *   policy_attempts.time_limit_minutes       the limit the attempt started with
 *   policy_attempts.expires_at               when that attempt's clock runs out
 *   policy_attempts.timed_out                the clock ran out before it was finished
 *   policy_attempt_answers.question_level    the level of the drawn question (tests mix levels)
 *
 * Whatever this migration adds is marked with a column comment, and down()
 * removes only marked items.
 */
return new class extends Migration
{
    const MARK = 'Added by 2026_10_01_100001';

    public function up(): void
    {
        if(Schema::hasTable('policy_documents') && !Schema::hasColumn('policy_documents', 'time_limit_minutes')):
            Schema::table('policy_documents', function (Blueprint $table) {
                $table->unsignedSmallInteger('time_limit_minutes')->nullable()->after('max_attempts')->comment(self::MARK);
            });
        endif;

        if(Schema::hasTable('policy_attempts')):
            if(!Schema::hasColumn('policy_attempts', 'time_limit_minutes')):
                Schema::table('policy_attempts', function (Blueprint $table) {
                    $table->unsignedSmallInteger('time_limit_minutes')->nullable()->after('passed')->comment(self::MARK);
                });
            endif;
            if(!Schema::hasColumn('policy_attempts', 'expires_at')):
                Schema::table('policy_attempts', function (Blueprint $table) {
                    $table->dateTime('expires_at')->nullable()->after('time_limit_minutes')->comment(self::MARK);
                });
            endif;
            if(!Schema::hasColumn('policy_attempts', 'timed_out')):
                Schema::table('policy_attempts', function (Blueprint $table) {
                    $table->boolean('timed_out')->default(0)->after('expires_at')->comment(self::MARK);
                });
            endif;
            if(!$this->hasIndex('policy_attempts', 'patt_status_expires_idx')):
                Schema::table('policy_attempts', function (Blueprint $table) {
                    $table->index(['status', 'expires_at'], 'patt_status_expires_idx');
                });
            endif;
        endif;

        if(Schema::hasTable('policy_attempt_answers') && !Schema::hasColumn('policy_attempt_answers', 'question_level')):
            Schema::table('policy_attempt_answers', function (Blueprint $table) {
                $table->string('question_level', 20)->nullable()->after('question_text')->comment(self::MARK);
            });
        endif;
    }

    public function down(): void
    {
        if(Schema::hasTable('policy_attempt_answers') && $this->isMarked('policy_attempt_answers', 'question_level')):
            Schema::table('policy_attempt_answers', function (Blueprint $table) {
                $table->dropColumn('question_level');
            });
        endif;

        if(Schema::hasTable('policy_attempts')):
            $added = array_values(array_filter(['time_limit_minutes', 'expires_at', 'timed_out'], function ($column) {
                return $this->isMarked('policy_attempts', $column);
            }));
            /* The index covers expires_at, so it only goes when this migration added that column. */
            if(in_array('expires_at', $added, true) && $this->hasIndex('policy_attempts', 'patt_status_expires_idx')):
                Schema::table('policy_attempts', function (Blueprint $table) {
                    $table->dropIndex('patt_status_expires_idx');
                });
            endif;
            if(!empty($added)):
                Schema::table('policy_attempts', function (Blueprint $table) use ($added) {
                    $table->dropColumn($added);
                });
            endif;
        endif;

        if(Schema::hasTable('policy_documents') && $this->isMarked('policy_documents', 'time_limit_minutes')):
            Schema::table('policy_documents', function (Blueprint $table) {
                $table->dropColumn('time_limit_minutes');
            });
        endif;
    }

    /** True when the column exists and carries this migration's comment. */
    protected function isMarked(string $table, string $column): bool
    {
        if(!Schema::hasColumn($table, $column)):
            return false;
        endif;

        $connection = Schema::getConnection();
        $rows = $connection->select('SHOW FULL COLUMNS FROM `'.$connection->getTablePrefix().$table.'` WHERE Field = ?', [$column]);

        return (!empty($rows) && isset($rows[0]->Comment) && $rows[0]->Comment === self::MARK);
    }

    protected function hasIndex(string $table, string $name): bool
    {
        $connection = Schema::getConnection();
        $rows = $connection->select('SHOW INDEX FROM `'.$connection->getTablePrefix().$table.'` WHERE Key_name = ?', [$name]);

        return !empty($rows);
    }
};
