<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Policy Assessments: policy roles, so HR assigns by role ("Lecturer",
 * "Finance") instead of picking categories or policies one by one.
 *
 * Catch-up for databases that ran 2026_09_30_100001 before it gained these —
 * a fresh install gets them from 100001 itself, and then this does nothing.
 * Every step is guarded and only ever adds, so existing rows keep their data:
 * existing assignments read as picked by hand (policy_role_id NULL).
 *
 *   policy_roles                         a role and whether it can be picked
 *   policy_role_documents                the policies ticked for a role
 *   policy_assignments.policy_role_id    the role an assignment came through
 *
 * These are this feature's own roles — not the roles table of the permission
 * system, which this does not touch.
 *
 * Whatever this migration adds is marked with a column/table comment, and
 * down() removes only marked items.
 */
return new class extends Migration
{
    const MARK = 'Added by 2026_10_01_100002';

    public function up(): void
    {
        if(!Schema::hasTable('policy_roles')):
            Schema::create('policy_roles', function (Blueprint $table) {
                $table->id();
                $table->string('name', 191);
                $table->text('description')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(1);

                $table->bigInteger('created_by')->unsigned()->nullable();
                $table->bigInteger('updated_by')->unsigned()->nullable();
                $table->softDeletes();
                $table->timestamps();

                $table->index('sort_order', 'pr_sort_idx');

                $table->comment(self::MARK);
            });
        endif;

        if(!Schema::hasTable('policy_role_documents')):
            Schema::create('policy_role_documents', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('policy_role_id')->unsigned();
                $table->bigInteger('policy_document_id')->unsigned();

                $table->bigInteger('created_by')->unsigned()->nullable();
                $table->timestamps();

                $table->index(['policy_role_id', 'policy_document_id'], 'prd_role_policy_idx');
                $table->index('policy_document_id', 'prd_policy_idx');

                $table->comment(self::MARK);
            });
        endif;

        if(Schema::hasTable('policy_assignments')):
            if(!Schema::hasColumn('policy_assignments', 'policy_role_id')):
                Schema::table('policy_assignments', function (Blueprint $table) {
                    $table->bigInteger('policy_role_id')->unsigned()->nullable()->after('level')->comment(self::MARK);
                });
            endif;
            if(!$this->hasIndex('policy_assignments', 'pa_role_idx')):
                Schema::table('policy_assignments', function (Blueprint $table) {
                    $table->index('policy_role_id', 'pa_role_idx');
                });
            endif;
        endif;
    }

    public function down(): void
    {
        if(Schema::hasTable('policy_assignments') && $this->isMarked('policy_assignments', 'policy_role_id')):
            /* The index covers policy_role_id, so it only goes when this migration added that column. */
            if($this->hasIndex('policy_assignments', 'pa_role_idx')):
                Schema::table('policy_assignments', function (Blueprint $table) {
                    $table->dropIndex('pa_role_idx');
                });
            endif;
            Schema::table('policy_assignments', function (Blueprint $table) {
                $table->dropColumn('policy_role_id');
            });
        endif;

        if(Schema::hasTable('policy_role_documents') && $this->tableComment('policy_role_documents') === self::MARK):
            Schema::drop('policy_role_documents');
        endif;

        if(Schema::hasTable('policy_roles') && $this->tableComment('policy_roles') === self::MARK):
            Schema::drop('policy_roles');
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
