<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Policy Assessments: a log of each time the test tab or window was left
 * during an attempt.
 *
 * Catch-up for databases that ran 2026_09_30_100001 before it gained these —
 * a fresh install gets them from 100001 itself, and then this does nothing.
 * Every step is guarded and only ever adds, so existing attempts keep their
 * data and read as "no exits".
 *
 *   policy_attempt_events          one row per exit: when, on which question, for how long
 *   policy_attempts.tab_exits      how many exits the attempt has
 *   policy_attempts.away_seconds   the time away, in total
 *
 * Whatever this migration adds is marked with a column/table comment, and
 * down() removes only marked items.
 */
return new class extends Migration
{
    const MARK = 'Added by 2026_10_01_100003';

    public function up(): void
    {
        if(Schema::hasTable('policy_attempts')):
            if(!Schema::hasColumn('policy_attempts', 'tab_exits')):
                Schema::table('policy_attempts', function (Blueprint $table) {
                    $table->unsignedSmallInteger('tab_exits')->default(0)->after('timed_out')->comment(self::MARK);
                });
            endif;
            if(!Schema::hasColumn('policy_attempts', 'away_seconds')):
                Schema::table('policy_attempts', function (Blueprint $table) {
                    $table->unsignedInteger('away_seconds')->default(0)->after('tab_exits')->comment(self::MARK);
                });
            endif;
        endif;

        if(!Schema::hasTable('policy_attempt_events')):
            Schema::create('policy_attempt_events', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('policy_attempt_id')->unsigned();
                /* 'tab' = switched tab or minimised the browser; 'window' = clicked outside the test window. */
                $table->string('type', 20)->default('window');
                /* The question on screen when they left; NULL on the review screen. */
                $table->unsignedSmallInteger('question_no')->nullable();
                $table->dateTime('left_at');
                /* NULL while they are still away. */
                $table->dateTime('returned_at')->nullable();
                $table->unsignedInteger('seconds')->nullable();
                $table->timestamps();

                $table->index(['policy_attempt_id', 'returned_at'], 'pae_attempt_idx');
                $table->comment(self::MARK);
            });
        endif;
    }

    public function down(): void
    {
        if(Schema::hasTable('policy_attempt_events') && $this->tableIsMarked('policy_attempt_events')):
            Schema::drop('policy_attempt_events');
        endif;

        if(Schema::hasTable('policy_attempts')):
            $added = array_values(array_filter(['tab_exits', 'away_seconds'], function ($column) {
                return $this->columnIsMarked('policy_attempts', $column);
            }));
            if(!empty($added)):
                Schema::table('policy_attempts', function (Blueprint $table) use ($added) {
                    $table->dropColumn($added);
                });
            endif;
        endif;
    }

    /** True when the column exists and carries this migration's comment. */
    protected function columnIsMarked(string $table, string $column): bool
    {
        if(!Schema::hasColumn($table, $column)):
            return false;
        endif;

        $connection = Schema::getConnection();
        $rows = $connection->select('SHOW FULL COLUMNS FROM `'.$connection->getTablePrefix().$table.'` WHERE Field = ?', [$column]);

        return (!empty($rows) && isset($rows[0]->Comment) && $rows[0]->Comment === self::MARK);
    }

    /** True when the table carries this migration's comment. */
    protected function tableIsMarked(string $table): bool
    {
        $connection = Schema::getConnection();
        $rows = $connection->select('SELECT TABLE_COMMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?', [$connection->getTablePrefix().$table]);

        return (!empty($rows) && isset($rows[0]->TABLE_COMMENT) && $rows[0]->TABLE_COMMENT === self::MARK);
    }
};
