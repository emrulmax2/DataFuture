<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Policy Assessments: levels (Beginner / Intermediate / Expert) and badges.
 *
 * Catch-up for databases that ran 2026_09_30_100001 before it gained these —
 * a fresh install gets them from 100001 itself, and then this does nothing.
 * Every step is guarded, so running it against either shape is safe, and it
 * only ever adds: existing rows keep their data and read as 'beginner'.
 *
 *   policy_questions.level      the level a question belongs to
 *   policy_questions.seed_key   stable identity of a seeded question
 *   policy_assignments.level    the level HR set the test at
 *   policy_attempts.level       the level an attempt was drawn at
 *   policy_badges               bronze / silver / gold badges earned by passing
 *
 * Whatever this migration adds is marked with a column/table comment, and
 * down() removes only marked items — so rolling it back on a database that
 * 100001 created (where it added nothing) leaves the schema, and the badges,
 * alone.
 */
return new class extends Migration
{
    const MARK = 'Added by 2026_09_30_100003';

    public function up(): void
    {
        if(Schema::hasTable('policy_questions')):
            if(!Schema::hasColumn('policy_questions', 'level')):
                Schema::table('policy_questions', function (Blueprint $table) {
                    $table->string('level', 20)->default('beginner')->after('origin')->comment(self::MARK);
                });
            endif;
            if(!Schema::hasColumn('policy_questions', 'seed_key')):
                Schema::table('policy_questions', function (Blueprint $table) {
                    $table->string('seed_key', 100)->nullable()->after('level')->comment(self::MARK);
                });
            endif;
            if(!$this->hasIndex('policy_questions', 'pq_policy_level_idx')):
                Schema::table('policy_questions', function (Blueprint $table) {
                    $table->index(['policy_document_id', 'level', 'is_active'], 'pq_policy_level_idx');
                });
            endif;
            if(!$this->hasIndex('policy_questions', 'pq_seed_key_idx')):
                Schema::table('policy_questions', function (Blueprint $table) {
                    $table->index('seed_key', 'pq_seed_key_idx');
                });
            endif;
        endif;

        if(Schema::hasTable('policy_assignments')):
            if(!Schema::hasColumn('policy_assignments', 'level')):
                Schema::table('policy_assignments', function (Blueprint $table) {
                    $table->string('level', 20)->default('beginner')->after('policy_document_id')->comment(self::MARK);
                });
            endif;
            if(!$this->hasIndex('policy_assignments', 'pa_emp_policy_level_idx')):
                Schema::table('policy_assignments', function (Blueprint $table) {
                    $table->index(['employee_id', 'policy_document_id', 'level'], 'pa_emp_policy_level_idx');
                });
            endif;
        endif;

        if(Schema::hasTable('policy_attempts') && !Schema::hasColumn('policy_attempts', 'level')):
            Schema::table('policy_attempts', function (Blueprint $table) {
                $table->string('level', 20)->default('beginner')->after('policy_document_id')->comment(self::MARK);
            });
        endif;

        if(!Schema::hasTable('policy_badges')):
            Schema::create('policy_badges', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('employee_id')->unsigned();
                $table->bigInteger('policy_document_id')->unsigned();
                $table->string('level', 20);
                $table->bigInteger('policy_assignment_id')->unsigned()->nullable();
                $table->bigInteger('policy_attempt_id')->unsigned()->nullable();
                $table->decimal('score', 5, 2)->nullable();
                $table->dateTime('awarded_at');
                $table->bigInteger('revoked_by')->unsigned()->nullable();

                $table->bigInteger('created_by')->unsigned()->nullable();
                $table->bigInteger('updated_by')->unsigned()->nullable();
                $table->softDeletes();
                $table->timestamps();

                $table->index('employee_id', 'pb_employee_idx');
                $table->index(['employee_id', 'policy_document_id', 'level'], 'pb_emp_policy_level_idx');

                $table->comment(self::MARK);
            });
        endif;
    }

    public function down(): void
    {
        if(Schema::hasTable('policy_badges') && $this->tableComment('policy_badges') === self::MARK):
            Schema::drop('policy_badges');
        endif;

        if($this->addedColumn('policy_attempts', 'level')):
            Schema::table('policy_attempts', function (Blueprint $table) {
                $table->dropColumn('level');
            });
        endif;

        if($this->addedColumn('policy_assignments', 'level')):
            $this->dropIndexIfExists('policy_assignments', 'pa_emp_policy_level_idx');
            Schema::table('policy_assignments', function (Blueprint $table) {
                $table->dropColumn('level');
            });
        endif;

        if($this->addedColumn('policy_questions', 'seed_key')):
            $this->dropIndexIfExists('policy_questions', 'pq_seed_key_idx');
            Schema::table('policy_questions', function (Blueprint $table) {
                $table->dropColumn('seed_key');
            });
        endif;

        if($this->addedColumn('policy_questions', 'level')):
            $this->dropIndexIfExists('policy_questions', 'pq_policy_level_idx');
            Schema::table('policy_questions', function (Blueprint $table) {
                $table->dropColumn('level');
            });
        endif;
    }

    protected function hasIndex(string $table, string $name): bool
    {
        $connection = Schema::getConnection();
        try {
            $indexes = $connection->getDoctrineSchemaManager()->listTableIndexes($connection->getTablePrefix().$table);

            return array_key_exists(strtolower($name), array_change_key_case($indexes, CASE_LOWER));
        } catch (\Throwable $e) {
            $rows = $connection->select('SHOW INDEX FROM `'.$connection->getTablePrefix().$table.'` WHERE Key_name = ?', [$name]);

            return !empty($rows);
        }
    }

    protected function dropIndexIfExists(string $table, string $name): void
    {
        if($this->hasIndex($table, $name)):
            Schema::table($table, function (Blueprint $blueprint) use ($name) {
                $blueprint->dropIndex($name);
            });
        endif;
    }

    /** Whether this migration added the column (it carries the marker comment). */
    protected function addedColumn(string $table, string $column): bool
    {
        if(!Schema::hasTable($table) || !Schema::hasColumn($table, $column)):
            return false;
        endif;

        $connection = Schema::getConnection();
        $row = $connection->selectOne(
            'SELECT COLUMN_COMMENT AS comment FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            [$connection->getTablePrefix().$table, $column]
        );

        return ($row && $row->comment === self::MARK);
    }

    protected function tableComment(string $table): ?string
    {
        $connection = Schema::getConnection();
        $row = $connection->selectOne(
            'SELECT TABLE_COMMENT AS comment FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?',
            [$connection->getTablePrefix().$table]
        );

        return ($row ? $row->comment : null);
    }
};
