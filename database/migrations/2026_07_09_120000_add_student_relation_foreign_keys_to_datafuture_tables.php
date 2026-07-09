<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->prepareDatafutureTable('student_course_session_datafutures');
        $this->prepareDatafutureTable('student_module_instance_datafutures');
        $this->prepareDatafutureTable('student_term_stuloads');

        $this->addIndexesAndForeigns(
            'student_course_session_datafutures',
            'scsdf_student_id_idx',
            'scsdf_scr_id_idx',
            'scsdf_ssi_id_idx',
            'scsdf_student_id_fk',
            'scsdf_scr_id_fk',
            'scsdf_ssi_id_fk'
        );

        $this->addIndexesAndForeigns(
            'student_module_instance_datafutures',
            'smidf_student_id_idx',
            'smidf_scr_id_idx',
            'smidf_ssi_id_idx',
            'smidf_student_id_fk',
            'smidf_scr_id_fk',
            'smidf_ssi_id_fk'
        );

        $this->addIndexesAndForeigns(
            'student_term_stuloads',
            'sts_student_id_idx',
            'sts_scr_id_idx',
            'sts_ssi_id_idx',
            'sts_student_id_fk',
            'sts_scr_id_fk',
            'sts_ssi_id_fk'
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->dropIndexesAndForeigns(
            'student_term_stuloads',
            ['sts_student_id_fk', 'sts_scr_id_fk', 'sts_ssi_id_fk'],
            ['sts_student_id_idx', 'sts_scr_id_idx', 'sts_ssi_id_idx']
        );

        $this->dropIndexesAndForeigns(
            'student_module_instance_datafutures',
            ['smidf_student_id_fk', 'smidf_scr_id_fk', 'smidf_ssi_id_fk'],
            ['smidf_student_id_idx', 'smidf_scr_id_idx', 'smidf_ssi_id_idx']
        );

        $this->dropIndexesAndForeigns(
            'student_course_session_datafutures',
            ['scsdf_student_id_fk', 'scsdf_scr_id_fk', 'scsdf_ssi_id_fk'],
            ['scsdf_student_id_idx', 'scsdf_scr_id_idx', 'scsdf_ssi_id_idx']
        );
    }

    private function prepareDatafutureTable(string $table): void
    {
        DB::statement("
            UPDATE {$table} t
            INNER JOIN student_stuload_information ssi
                ON ssi.id = t.student_stuload_information_id
            SET
                t.student_id = ssi.student_id,
                t.student_course_relation_id = ssi.student_course_relation_id
        ");

        DB::statement("
            DELETE t
            FROM {$table} t
            LEFT JOIN student_stuload_information ssi
                ON ssi.id = t.student_stuload_information_id
            WHERE ssi.id IS NULL
        ");

        DB::statement("
            DELETE t
            FROM {$table} t
            LEFT JOIN students s
                ON s.id = t.student_id
            LEFT JOIN student_course_relations scr
                ON scr.id = t.student_course_relation_id
            WHERE s.id IS NULL
               OR scr.id IS NULL
        ");
    }

    private function addIndexesAndForeigns(
        string $tableName,
        string $studentIndex,
        string $relationIndex,
        string $stuloadIndex,
        string $studentForeign,
        string $relationForeign,
        string $stuloadForeign
    ): void {
        if (!$this->indexExists($tableName, $studentIndex)) {
            Schema::table($tableName, function (Blueprint $table) use ($studentIndex) {
                $table->index('student_id', $studentIndex);
            });
        }

        if (!$this->indexExists($tableName, $relationIndex)) {
            Schema::table($tableName, function (Blueprint $table) use ($relationIndex) {
                $table->index('student_course_relation_id', $relationIndex);
            });
        }

        if (!$this->indexExists($tableName, $stuloadIndex)) {
            Schema::table($tableName, function (Blueprint $table) use ($stuloadIndex) {
                $table->index('student_stuload_information_id', $stuloadIndex);
            });
        }

        if (!$this->foreignExists($tableName, $studentForeign)) {
            Schema::table($tableName, function (Blueprint $table) use ($studentForeign) {
                $table->foreign('student_id', $studentForeign)
                    ->references('id')
                    ->on('students')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            });
        }

        if (!$this->foreignExists($tableName, $relationForeign)) {
            Schema::table($tableName, function (Blueprint $table) use ($relationForeign) {
                $table->foreign('student_course_relation_id', $relationForeign)
                    ->references('id')
                    ->on('student_course_relations')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            });
        }

        if (!$this->foreignExists($tableName, $stuloadForeign)) {
            Schema::table($tableName, function (Blueprint $table) use ($stuloadForeign) {
                $table->foreign('student_stuload_information_id', $stuloadForeign)
                    ->references('id')
                    ->on('student_stuload_information')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            });
        }
    }

    private function dropIndexesAndForeigns(string $tableName, array $foreigns, array $indexes): void
    {
        foreach ($foreigns as $foreign) {
            if ($this->foreignExists($tableName, $foreign)) {
                Schema::table($tableName, function (Blueprint $table) use ($foreign) {
                    $table->dropForeign($foreign);
                });
            }
        }

        foreach ($indexes as $index) {
            if ($this->indexExists($tableName, $index)) {
                Schema::table($tableName, function (Blueprint $table) use ($index) {
                    $table->dropIndex($index);
                });
            }
        }
    }

    private function foreignExists(string $tableName, string $foreignName): bool
    {
        return DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $tableName)
            ->where('CONSTRAINT_NAME', $foreignName)
            ->where('CONSTRAINT_TYPE', 'FOREIGN KEY')
            ->exists();
    }

    private function indexExists(string $tableName, string $indexName): bool
    {
        return DB::table('information_schema.STATISTICS')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $tableName)
            ->where('INDEX_NAME', $indexName)
            ->exists();
    }
};
