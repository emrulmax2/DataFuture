<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Writes the HESA Data Futures student report to a CSV file and prints a link
 * to download it.
 *
 * One row per student: their latest course session that the XML export would
 * write, the engagement it belongs to and its latest module, with the name of
 * each code beside it. With no arguments it reports the 2025-26 list of
 * students held at the bottom of this file.
 *
 * Everything it needs is in this one file - the query and the list - so it is
 * the only thing to upload. The query is the same one kept for running by hand
 * in database/sql/datafuture_report_by_registration_no.sql; it runs here
 * because the result, about a hundred columns for a couple of thousand
 * students, is more than phpMyAdmin will hand back through a browser.
 *
 * The file is full of personal data and has to be reachable by a link, so it
 * goes on the public disk under a name nobody could guess, and nothing lists
 * that folder. Anyone holding the link can still open it: download the file,
 * then run the command again with --delete.
 */
class DatafutureStudentReport extends Command
{
    protected $signature = 'datafuture:student-report
        {numbers? : A text file of registration numbers, to use instead of the built-in 2025-26 list.}
        {--all : Every student the XML export would include, instead of a list.}
        {--delete : Remove the report files earlier runs left behind, and do nothing else.}
        {--chunk=300 : How many students to read per query.}';

    protected $description = 'Write the Data Futures student report (one row per student) to a CSV file and print its download link.';

    /** Where the reports go on the public disk, and what every report file starts with. */
    private const FOLDER = 'reports';
    private const PREFIX = 'datafuture_student_report_';

    public function handle(): int
    {
        if ($this->option('delete')) {
            return $this->deleteReports();
        }

        $all = (bool) $this->option('all');
        $numbers = $all ? [] : $this->numbers();

        if (! $all && $numbers === null) {
            return self::FAILURE;
        }

        $disk = Storage::disk('public');
        $disk->makeDirectory(self::FOLDER);

        // The random part is what keeps the link private: without it the name
        // could be worked out from the date.
        $name = self::FOLDER.'/'.self::PREFIX.date('Ymd_His').'_'.Str::random(40).'.csv';
        $file = fopen($disk->path($name), 'w');
        // The mark Excel needs to read the file as UTF-8, or accented names come out wrong.
        fwrite($file, "\xEF\xBB\xBF");

        $written = 0;
        $found = [];
        $write = function (array $rows) use ($file, &$written, &$found) {
            foreach ($rows as $row) {
                $row = (array) $row;

                if ($written === 0) {
                    fputcsv($file, array_keys($row));
                }

                fputcsv($file, $row);
                $found[$row['OWNSTU']] = true;
                $written++;
            }
        };

        if ($all) {
            $write(DB::select($this->query('')));
        } else {
            $pdo = DB::getPdo();
            $chunks = array_chunk($numbers, max(1, (int) $this->option('chunk')));

            $this->withProgressBar($chunks, function (array $chunk) use ($pdo, $write) {
                $list = implode(', ', array_map(fn ($number) => $pdo->quote($number), $chunk));

                $write(DB::select($this->query('AND s.registration_no IN ('.$list.')')));
            });
            $this->newLine(2);
        }

        fclose($file);

        $this->info($written.' '.($written === 1 ? 'student' : 'students').' written.');

        // A number can be left out for a reason the export itself would leave it out, so say which.
        $missing = array_values(array_filter($numbers, fn ($number) => ! isset($found[$number])));

        if ($missing) {
            $this->warn(count($missing).' of the '.count($numbers).' numbers have no row: '.implode(', ', array_slice($missing, 0, 20)).(count($missing) > 20 ? ', ...' : ''));
            $this->line('A student is left out when the record does not exist, HESA status is off, or they have');
            $this->line('no stuload marked visible for the report on a course other than 30 / 31.');
        }

        $this->newLine();
        $this->line('Download: '.$disk->url($name));
        $this->line('On disk:  '.$disk->path($name));
        $this->newLine();
        $this->warn('This file holds personal data and anyone with the link can open it.');
        $this->warn('Once it is downloaded, remove it:  php artisan datafuture:student-report --delete');

        return self::SUCCESS;
    }

    private function deleteReports(): int
    {
        $disk = Storage::disk('public');
        $reports = array_filter(
            $disk->files(self::FOLDER),
            fn ($path) => str_starts_with(basename($path), self::PREFIX) && str_ends_with($path, '.csv')
        );

        $disk->delete($reports);
        $this->info(count($reports).' report '.(count($reports) === 1 ? 'file' : 'files').' removed.');

        return self::SUCCESS;
    }

    /** @return array<int, string>|null the registration numbers to report, or null when a given file cannot be used */
    private function numbers(): ?array
    {
        $source = $this->argument('numbers');

        if (! $source) {
            return preg_split('/\s+/', trim(self::STUDENTS));
        }

        if (! is_file($source)) {
            $this->error('No such file: '.$source);

            return null;
        }

        $numbers = array_values(array_unique(
            preg_split('/[\s,;"\']+/', (string) file_get_contents($source), -1, PREG_SPLIT_NO_EMPTY)
        ));

        if (! $numbers) {
            $this->error('That file has no registration numbers in it.');

            return null;
        }

        return $numbers;
    }

    /** The report query, with $students as its condition on which students to read ('' for all). */
    private function query(string $students): string
    {
        return str_replace('{{students}}', $students, self::SQL);
    }

    /**
     * The report. {{students}} is where the registration number condition goes.
     *
     * Same rules as the XML export: the student must have HESA status on, only
     * stuloads marked visible for the report are read, and the student needs at
     * least one of them on a course other than 30 / 31. There is no date range.
     */
    private const SQL = <<<'SQL'
WITH
st AS (
    SELECT s.*
    FROM students s
    WHERE s.deleted_at IS NULL
      AND s.hesa_status = 1
      AND EXISTS (
            SELECT 1
            FROM student_stuload_information i
            JOIN student_course_relations r ON r.id = i.student_course_relation_id AND r.deleted_at IS NULL
            JOIN course_creations c ON c.id = r.course_creation_id AND c.deleted_at IS NULL
            WHERE i.student_id = s.id AND i.report_visibility = 1 AND i.deleted_at IS NULL
              AND c.course_id NOT IN (30, 31)
      )
      {{students}}
),

/* Every visible course session (stuload) of the student, with everything
   above module level. The latest one is picked further down. */
sess AS (
    SELECT
        s.id                              AS student_id,
        scr.id                            AS crel_id,
        ssi.id                            AS stuload_id,
        scr.course_creation_id            AS course_creation_id,
        cc.course_id                      AS course_id,
        crs.name                          AS course_name,
        scr.active                        AS crel_active,

        /* Not in the XML. The status is the student's own; the intake is the
           semester of their active course relation, as shown on their profile. */
        stt.name                          AS STUDENT_STATUS,
        asem.name                         AS INTAKE_SEMESTER,

        /* The student's next enrolment on the same course: a session only
           reports modules from its own course creation up to that one. */
        (SELECT MIN(n.course_creation_id)
           FROM student_course_relations n
           JOIN course_creations ncc ON ncc.id = n.course_creation_id AND ncc.deleted_at IS NULL
          WHERE n.student_id = s.id AND n.deleted_at IS NULL
            AND n.course_creation_id > scr.course_creation_id
            AND ncc.course_id = cc.course_id)                                   AS next_course_creation_id,

        /* ───────────── Student ───────────── */
        NULLIF(lsl.sid_number, '')                                              AS SID,
        DATE_FORMAT(s.date_of_birth, '%Y-%m-%d')                                AS BIRTHDTE,
        NULLIF(eth.df_code, '')                                                 AS ETHNIC,
        eth.name                                                                AS ETHNIC_NAME,
        NULLIF(s.first_name, '')                                                AS FNAMES,
        NULLIF(gen.df_code, '')                                                 AS GENDERID,
        gen.name                                                                AS GENDERID_NAME,
        NULLIF(nat.df_code, '')                                                 AS NATION,
        nat.name                                                                AS NATION_NAME,
        s.registration_no                                                       AS OWNSTU,
        NULLIF(rlg.df_code, '')                                                 AS RELIGION,
        rlg.name                                                                AS RELIGION_NAME,
        NULLIF(sxi.df_code, '')                                                 AS SEXID,
        sxi.name                                                                AS SEXID_NAME,
        NULLIF(sxo.df_code, '')                                                 AS SEXORT,
        sxo.name                                                                AS SEXORT_NAME,
        NULLIF(s.ssn_no, '')                                                    AS SSN,
        NULLIF(s.last_name, '')                                                 AS SURNAME,
        NULLIF(tta.df_code, '')                                                 AS TTACCOM,
        tta.name                                                                AS TTACCOM_NAME,
        NULLIF(sct.term_time_post_code, '')                                     AS TTPCODE,

        /* Disability: the student's codes, or 95 when none are recorded. */
        CASE
            WHEN sod.disability_status = 1
             AND EXISTS (SELECT 1 FROM student_disabilities d WHERE d.student_id = s.id AND d.deleted_at IS NULL)
            THEN (SELECT GROUP_CONCAT(dis.df_code ORDER BY d.id SEPARATOR ', ')
                    FROM student_disabilities d
                    JOIN disabilities dis ON dis.id = d.disability_id AND dis.deleted_at IS NULL
                   WHERE d.student_id = s.id AND d.deleted_at IS NULL AND dis.df_code <> '')
            ELSE '95'
        END                                                                     AS DISABILITY,
        CASE
            WHEN sod.disability_status = 1
             AND EXISTS (SELECT 1 FROM student_disabilities d WHERE d.student_id = s.id AND d.deleted_at IS NULL)
            THEN (SELECT GROUP_CONCAT(dis.name ORDER BY d.id SEPARATOR ', ')
                    FROM student_disabilities d
                    JOIN disabilities dis ON dis.id = d.disability_id AND dis.deleted_at IS NULL
                   WHERE d.student_id = s.id AND d.deleted_at IS NULL AND dis.df_code <> '')
            ELSE (SELECT l.name FROM disabilities l
                   WHERE l.df_code = '95' AND l.deleted_at IS NULL ORDER BY l.active DESC, l.id LIMIT 1)
        END                                                                     AS DISABILITY_NAME,

        /* ───────────── Engagement ───────────── */
        COALESCE(NULLIF(sdf.NUMHUS, ''), '1')                                   AS NUMHUS,
        DATE_FORMAT(COALESCE(scr.course_end_date, cca.course_end_date), '%Y-%m-%d')     AS ENGEXPECTEDENDDATE,
        DATE_FORMAT(COALESCE(scr.course_start_date, cca.course_start_date), '%Y-%m-%d') AS ENGSTARTDATE,
        NULLIF(sem.name, '')                                                    AS OWNENGID,
        NULLIF(fel.df_code, '')                                                 AS FEEELIG,
        fel.name                                                                AS FEEELIG_NAME,

        /* Entry profile */
        NULLIF(clv.df_code, '')                                                 AS CARELEAVER,
        clv.name                                                                AS CARELEAVER_NAME,
        NULLIF(pco.df_code, '')                                                 AS PERMADDCOUNTRY,
        TRIM(REPLACE(REPLACE(pco.name, '\r', ''), '\n', ''))                    AS PERMADDCOUNTRY_NAME,
        NULLIF(sct.permanent_post_code, '')                                     AS PERMADDPOSTCODE,
        CASE WHEN sod.is_education_qualification = 1 THEN NULLIF(ppv.df_code, '') END   AS PREVIOUSPROVIDER,
        CASE WHEN sod.is_education_qualification = 1 THEN ppv.name END                  AS PREVIOUSPROVIDER_NAME,
        CASE WHEN sod.is_education_qualification = 1 THEN NULLIF(hqe.df_code, '') END   AS HIGHESTQOE,
        CASE WHEN sod.is_education_qualification = 1 THEN hqe.name END                  AS HIGHESTQOE_NAME,

        /* Entry qualification award (the student's latest qualification).
           ENTRYQUALAWARDID is already a name, so it has no _NAME column. */
        CASE WHEN sod.is_education_qualification = 1 THEN NULLIF(oaq.name, '') END      AS ENTRYQUALAWARDID,
        CASE WHEN sod.is_education_qualification = 1 THEN NULLIF(qgr.df_code, '') END   AS ENTRYQUALAWARDRESULT,
        CASE WHEN sod.is_education_qualification = 1 THEN qgr.name END                  AS ENTRYQUALAWARDRESULT_NAME,
        CASE WHEN sod.is_education_qualification = 1 THEN NULLIF(qti.df_code, '') END   AS QUALTYPEID,
        CASE WHEN sod.is_education_qualification = 1 THEN qti.name END                  AS QUALTYPEID_NAME,
        CASE WHEN sod.is_education_qualification = 1 THEN NULLIF(YEAR(sq.degree_award_date), 0) END AS QUALYEAR,
        CASE WHEN sod.is_education_qualification = 1 THEN NULLIF(hqs.df_code, '') END   AS SUBJECTID,
        CASE WHEN sod.is_education_qualification = 1 THEN hqs.name END                  AS SUBJECTID_NAME,

        /* Leaver: only when the course relation is active and the student's
           status is an ending one that matches their latest term status. */
        CASE WHEN scr.active = 1 AND s.status_id = ts.status_id
              AND s.status_id IN (21, 26, 27, 31, 42, 13, 16, 17, 33)
             THEN DATE_FORMAT(ts.status_end_date, '%Y-%m-%d') END               AS ENGENDDATE,
        CASE WHEN scr.active = 1 AND s.status_id = ts.status_id
              AND s.status_id IN (21, 26, 27, 31, 42, 13, 16, 17, 33)
             THEN NULLIF(ree.df_code, '') END                                   AS RSNENGEND,
        CASE WHEN scr.active = 1 AND s.status_id = ts.status_id
              AND s.status_id IN (21, 26, 27, 31, 42, 13, 16, 17, 33)
             THEN ree.name END                                                  AS RSNENGEND_NAME,

        /* Qualification awarded */
        NULLIF(awd.qual_award_type, '')                                         AS QUALAWARDID,
        CASE WHEN NULLIF(awd.qual_award_type, '') IS NOT NULL THEN
            (SELECT NULLIF(TRIM(b.field_value), '')
               FROM course_base_datafutures b
               JOIN datafuture_fields f ON f.id = b.datafuture_field_id AND f.deleted_at IS NULL
              WHERE b.course_id = cc.course_id AND b.deleted_at IS NULL
                AND f.datafuture_field_category_id = 2 AND f.name = 'QUALID'
              ORDER BY b.id DESC LIMIT 1)
        END                                                                     AS QUALID,
        NULLIF(qar.df_code, '')                                                 AS QUALAWARDRESULT,
        qar.name                                                                AS QUALAWARDRESULT_NAME,

        /* ───────────── Student course session ───────────── */
        ssi.course_creation_instance_id                                         AS SCSESSIONID,
        ssi.courseaim_id                                                        AS COURSEID,
        aim.name                                                                AS COURSEID_NAME,
        NULLIF(ssi.gross_fee, 0)                                                AS INVOICEFEEAMOUNT,
        COALESCE(NULLIF(csd.INVOICEHESAID, ''), '5026')                         AS INVOICEHESAID,
        CASE WHEN ssi.netfee > 0 THEN ssi.netfee END                            AS SCSFEEAMOUNT,
        CASE WHEN smd.df_code > 0 THEN smd.df_code ELSE '01' END                AS SCSMODE,
        CASE WHEN smd.df_code > 0 THEN smd.name
             ELSE (SELECT l.name FROM study_modes l
                    WHERE l.df_code = '01' AND l.deleted_at IS NULL ORDER BY l.active DESC, l.id LIMIT 1)
        END                                                                     AS SCSMODE_NAME,
        NULLIF(DATE_FORMAT(ssi.periodstart, '%Y-%m-%d'), '0000-00-00')          AS SCSSTARTDATE,
        ssi.course_creation_instance_id                                         AS SESSIONYEARID,
        CASE WHEN ssi.yearprg > 0 THEN ssi.yearprg END                          AS YEARPRG,

        /* Funding and monitoring - the values held on the session */
        NULLIF(elq.df_code, '')                                                 AS ELQ,
        elq.name                                                                AS ELQ_NAME,
        NULLIF(fcp.df_code, '')                                                 AS fundcomp_set,
        COALESCE(NULLIF(fln.df_code, ''), '96')                                 AS FUNDLENGTH,
        NULLIF(nrf.df_code, '')                                                 AS NONREGFEE,
        nrf.name                                                                AS NONREGFEE_NAME,

        /* Reference period student load. The XML adds up only the terms that
           start inside the chosen date range; with no range this is every term
           of the session. */
        '01'                                                                    AS REFPERIOD,
        YEAR(acy.from_date)                                                     AS `YEAR`,
        (SELECT SUM(tl.student_load)
           FROM instance_terms it
           JOIN student_term_stuloads tl
             ON tl.id = (SELECT MIN(z.id) FROM student_term_stuloads z
                          WHERE z.student_id = s.id AND z.student_course_relation_id = scr.id
                            AND z.student_stuload_information_id = ssi.id
                            AND z.instance_term_id = it.id AND z.deleted_at IS NULL)
          WHERE it.course_creation_instance_id = ssi.course_creation_instance_id
            AND it.deleted_at IS NULL AND tl.student_load > 0)                  AS rp_load,

        NULLIF(csd.FINSUPTYPE, '')                                              AS FINSUPTYPE,

        /* Study location */
        NULLIF(ven.name, '')                                                    AS STUDYLOCID,
        COALESCE(NULLIF(csd.STUDYPROPORTION, ''), '100')                        AS STUDYPROPORTION,
        NULLIF(ven.idnumber, '')                                                AS VENUEID,

        /* Working values for the rules applied further down. Kept as text
           ('' when missing) because the export compares them as text. */
        NULLIF(csd.RSNSCSEND, '')                                               AS rsnscsend_set,
        COALESCE(DATE_FORMAT(cci.start_date, '%Y-%m-%d'), '')                   AS instance_start,
        COALESCE(DATE_FORMAT(cci.end_date, '%Y-%m-%d'), '')                     AS instance_end,
        COALESCE(DATE_FORMAT(ssi.enddate, '%Y-%m-%d'), '')                      AS hesa_end,
        COALESCE(NULLIF(DATE_FORMAT(ssi.periodstart, '%Y-%m-%d'), '0000-00-00'), '') AS period_start,
        COALESCE(NULLIF(DATE_FORMAT(ssi.periodend, '%Y-%m-%d'), '0000-00-00'), '')   AS period_end,
        DATE_FORMAT(CURDATE(), '%Y-%m-%d')                                      AS today
    FROM st s
    JOIN student_stuload_information ssi
           ON ssi.student_id = s.id AND ssi.report_visibility = 1 AND ssi.deleted_at IS NULL
    JOIN student_course_relations scr
           ON scr.id = ssi.student_course_relation_id AND scr.deleted_at IS NULL
    LEFT JOIN course_creations cc      ON cc.id = scr.course_creation_id AND cc.deleted_at IS NULL
    LEFT JOIN courses crs              ON crs.id = cc.course_id AND crs.deleted_at IS NULL
    LEFT JOIN courses aim              ON aim.id = ssi.courseaim_id AND aim.deleted_at IS NULL
    LEFT JOIN semesters sem            ON sem.id = cc.semester_id AND sem.deleted_at IS NULL
    LEFT JOIN course_creation_availabilities cca
           ON cca.id = (SELECT MAX(x.id) FROM course_creation_availabilities x
                         WHERE x.course_creation_id = cc.id AND x.deleted_at IS NULL)

    /* Student details. Where a student has more than one row the export
       reads the first (details, contact) or the latest (qualification). */
    LEFT JOIN student_other_details sod
           ON sod.id = (SELECT MIN(x.id) FROM student_other_details x WHERE x.student_id = s.id AND x.deleted_at IS NULL)
    LEFT JOIN ethnicities eth          ON eth.id = sod.ethnicity_id AND eth.deleted_at IS NULL
    LEFT JOIN hesa_genders gen         ON gen.id = sod.hesa_gender_id AND gen.deleted_at IS NULL
    LEFT JOIN religions rlg            ON rlg.id = sod.religion_id AND rlg.deleted_at IS NULL
    LEFT JOIN sexual_orientations sxo  ON sxo.id = sod.sexual_orientation_id AND sxo.deleted_at IS NULL
    LEFT JOIN care_leavers clv         ON clv.id = sod.care_leaver_id AND clv.deleted_at IS NULL
    LEFT JOIN study_modes smd          ON smd.id = sod.study_mode_id AND smd.deleted_at IS NULL
    LEFT JOIN countries nat            ON nat.id = s.nationality_id AND nat.deleted_at IS NULL
    LEFT JOIN statuses stt             ON stt.id = s.status_id AND stt.deleted_at IS NULL
    LEFT JOIN sex_identifiers sxi      ON sxi.id = s.sex_identifier_id AND sxi.deleted_at IS NULL
    LEFT JOIN student_contacts sct
           ON sct.id = (SELECT MIN(x.id) FROM student_contacts x WHERE x.student_id = s.id AND x.deleted_at IS NULL)
    LEFT JOIN term_time_accommodation_types tta ON tta.id = sct.term_time_accommodation_type_id AND tta.deleted_at IS NULL
    LEFT JOIN country_of_permanent_addresses pco ON pco.id = sct.permanent_country_id AND pco.deleted_at IS NULL
    LEFT JOIN student_qualifications sq
           ON sq.id = (SELECT MAX(x.id) FROM student_qualifications x WHERE x.student_id = s.id AND x.deleted_at IS NULL)
    LEFT JOIN previous_providers ppv            ON ppv.id = sq.previous_provider_id AND ppv.deleted_at IS NULL
    LEFT JOIN highest_qualification_of_entry hqe ON hqe.id = sq.highest_qualification_on_entry_id AND hqe.deleted_at IS NULL
    LEFT JOIN other_academic_qualifications oaq ON oaq.id = sq.other_academic_qualification_id AND oaq.deleted_at IS NULL
    LEFT JOIN qualification_grades qgr          ON qgr.id = sq.qualification_grade_id AND qgr.deleted_at IS NULL
    LEFT JOIN entry_qualification_type qti      ON qti.id = sq.qualification_type_identifier_id AND qti.deleted_at IS NULL
    LEFT JOIN entry_qualification_subject hqs   ON hqs.id = sq.hesa_qualification_subject_id AND hqs.deleted_at IS NULL

    /* The student's latest term status, for the Leaver block. */
    LEFT JOIN student_attendance_term_statuses ts
           ON ts.id = (SELECT x.id FROM student_attendance_term_statuses x
                        WHERE x.student_id = s.id AND x.deleted_at IS NULL
                        ORDER BY x.term_declaration_id DESC, x.id DESC LIMIT 1)
    LEFT JOIN reason_for_engagement_endings ree ON ree.id = ts.reason_for_engagement_ending_id AND ree.deleted_at IS NULL

    /* SID and NUMHUS hang off the student's active course relation (the first
       one flagged active), not off the relation being reported. */
    LEFT JOIN student_course_relations acr
           ON acr.id = (SELECT MIN(x.id) FROM student_course_relations x
                         WHERE x.student_id = s.id AND x.active = 1 AND x.deleted_at IS NULL)
    LEFT JOIN course_creations acc     ON acc.id = acr.course_creation_id AND acc.deleted_at IS NULL
    LEFT JOIN semesters asem           ON asem.id = acc.semester_id AND asem.deleted_at IS NULL
    LEFT JOIN student_stuload_information lsl
           ON lsl.id = (SELECT MAX(x.id) FROM student_stuload_information x
                         WHERE x.student_id = s.id AND x.student_course_relation_id = acr.id AND x.deleted_at IS NULL)
    LEFT JOIN student_datafutures sdf
           ON sdf.id = (SELECT MAX(x.id) FROM student_datafutures x
                         WHERE x.student_id = s.id AND x.student_course_relation_id = acr.id AND x.deleted_at IS NULL)

    /* Per course relation */
    LEFT JOIN student_fee_eligibilities sfe
           ON sfe.id = (SELECT MAX(x.id) FROM student_fee_eligibilities x
                         WHERE x.student_course_relation_id = scr.id AND x.deleted_at IS NULL)
    LEFT JOIN fee_eligibilities fel    ON fel.id = sfe.fee_eligibility_id AND fel.deleted_at IS NULL
    LEFT JOIN student_proposed_courses spc
           ON spc.id = (SELECT MAX(x.id) FROM student_proposed_courses x
                         WHERE x.student_course_relation_id = scr.id AND x.deleted_at IS NULL)
    LEFT JOIN venues ven               ON ven.id = spc.venue_id AND ven.deleted_at IS NULL
    /* The first award per course relation, found in one pass: student_awards
       has no index on the student, so a lookup per row would scan it each time. */
    LEFT JOIN (SELECT w.student_id, w.student_course_relation_id, MIN(w.id) AS id
                 FROM student_awards w
                WHERE w.deleted_at IS NULL
                GROUP BY w.student_id, w.student_course_relation_id) faw
           ON faw.student_id = s.id AND faw.student_course_relation_id = scr.id
    LEFT JOIN student_awards awd       ON awd.id = faw.id
    LEFT JOIN qual_award_results qar   ON qar.id = awd.qual_award_result_id AND qar.deleted_at IS NULL

    /* Per course session */
    LEFT JOIN course_creation_instances cci ON cci.id = ssi.course_creation_instance_id AND cci.deleted_at IS NULL
    LEFT JOIN academic_years acy            ON acy.id = cci.academic_year_id AND acy.deleted_at IS NULL
    LEFT JOIN student_course_session_datafutures csd
           ON csd.id = (SELECT MIN(x.id) FROM student_course_session_datafutures x
                         WHERE x.student_stuload_information_id = ssi.id AND x.deleted_at IS NULL)
    LEFT JOIN equivalent_or_lower_qualifications elq ON elq.id = csd.ELQ AND elq.deleted_at IS NULL
    LEFT JOIN funding_completions fcp        ON fcp.id = csd.FUNDCOMP AND fcp.deleted_at IS NULL
    LEFT JOIN funding_lengths fln            ON fln.id = csd.FUNDLENGTH AND fln.deleted_at IS NULL
    LEFT JOIN non_regulated_fee_flags nrf    ON nrf.id = csd.NONREGFEE AND nrf.deleted_at IS NULL
),

/* Session status: each term of a session, whether the student was suspended
   in it, and whether a suspension has been seen yet in that session. */
term_state AS (
    SELECT
        x.stuload_id,
        x.student_id,
        it.id AS term_id,
        it.term_declaration_id,
        CASE WHEN tst.status_id IN (17, 27, 30, 31, 33, 36) THEN 1 ELSE 0 END  AS suspended,
        DATE_FORMAT(td.start_date, '%Y-%m-%d')                                  AS term_start,
        MAX(CASE WHEN tst.status_id IN (17, 27, 30, 31, 33, 36) THEN 1 ELSE 0 END)
            OVER (PARTITION BY x.stuload_id ORDER BY it.id)                     AS suspension_seen
    FROM sess x
    JOIN instance_terms it ON it.course_creation_instance_id = x.SCSESSIONID AND it.deleted_at IS NULL
    LEFT JOIN term_declarations td ON td.id = it.term_declaration_id AND td.deleted_at IS NULL
    LEFT JOIN student_attendance_term_statuses tst
           ON tst.id = (SELECT MAX(z.id) FROM student_attendance_term_statuses z
                         WHERE z.student_id = x.student_id AND z.term_declaration_id = it.term_declaration_id
                           AND z.deleted_at IS NULL)
),
/* Only the terms that produce a status line. A suspended term is dated from
   the student's last counted attendance in it, any other from the term start;
   the attendance lookup therefore runs for suspended terms alone. */
term_status AS (
    SELECT
        t.stuload_id,
        t.term_id,
        t.suspended,
        CASE
            WHEN t.suspended = 1 THEN
                (SELECT DATE_FORMAT(MAX(a.attendance_date), '%Y-%m-%d')
                   FROM attendances a
                   JOIN plans ap ON ap.id = a.plan_id AND ap.deleted_at IS NULL
                   JOIN attendance_feed_statuses fs ON fs.id = a.attendance_feed_status_id AND fs.attendance_count = 1
                  WHERE a.student_id = t.student_id AND a.deleted_at IS NULL
                    AND ap.term_declaration_id = t.term_declaration_id)
            ELSE t.term_start
        END                                                                     AS valid_from
    FROM term_state t
    WHERE t.suspension_seen = 1
),
session_status AS (
    SELECT
        t.stuload_id,
        GROUP_CONCAT(CONCAT(COALESCE(t.valid_from, ''), ' = ', COALESCE(ss.df_code, ''))
                     ORDER BY t.term_id SEPARATOR '; ')                         AS SESSIONSTATUS,
        GROUP_CONCAT(CONCAT(COALESCE(t.valid_from, ''), ' = ', COALESCE(ss.name, ''))
                     ORDER BY t.term_id SEPARATOR '; ')                         AS SESSIONSTATUS_NAME
    FROM term_status t
    LEFT JOIN session_statuses ss
           ON ss.id = CASE WHEN t.suspended = 1 THEN 2 ELSE 1 END AND ss.deleted_at IS NULL
    GROUP BY t.stuload_id
),

/* Module instances: taught classes on the session's course that the student
   is assigned to and has attendance on within the session period. Tutorials,
   seminars, practicals and the group / personal tutorial modules are left out.

   Done in two steps so the attendance table is read once per student: first
   the classes each session has attendance on, then the rules on those few. */
attended AS (
    SELECT DISTINCT x.stuload_id, a.plan_id
    FROM sess x
    JOIN attendances a
          ON a.student_id = x.student_id AND a.deleted_at IS NULL
         AND a.attendance_date BETWEEN x.period_start AND x.period_end
    WHERE x.period_start <> '' AND x.period_end <> ''
),
module_instance AS (
    SELECT t.stuload_id, t.plan_id
    FROM attended t
    JOIN sess x  ON x.stuload_id = t.stuload_id
    JOIN plans p ON p.id = t.plan_id
                AND p.deleted_at IS NULL
                AND p.course_id = x.course_id
                AND p.course_creation_id >= x.course_creation_id
                AND (x.next_course_creation_id IS NULL OR p.course_creation_id < x.next_course_creation_id)
                AND (p.class_type IS NULL OR p.class_type NOT IN ('Tutorial', 'Seminar', 'Practical'))
    WHERE EXISTS (SELECT 1 FROM assigns g
                   WHERE g.student_id = x.student_id AND g.plan_id = p.id AND g.deleted_at IS NULL)
      AND NOT EXISTS (SELECT 1 FROM module_creations m
                       WHERE m.id = p.module_creation_id AND m.deleted_at IS NULL
                         AND (m.module_name LIKE '%GROUP TUTORIAL (QCF)%'
                           OR m.module_name LIKE '%GROUP TUTORIAL (RQF)%'
                           OR m.module_name LIKE '%PERSONAL TUTORIAL%'))
),
session_modules AS (
    SELECT
        mi.stuload_id,
        COUNT(*)                                                                AS module_count,
        MAX(mi.plan_id)                                                         AS latest_plan_id,
        GROUP_CONCAT(CONCAT(mi.plan_id, ':', COALESCE(mc.course_module_id, ''))
                     ORDER BY mi.plan_id DESC SEPARATOR ', ')                   AS module_list
    FROM module_instance mi
    JOIN plans p ON p.id = mi.plan_id
    LEFT JOIN module_creations mc ON mc.id = p.module_creation_id AND mc.deleted_at IS NULL
    GROUP BY mi.stuload_id
),

/* The student's sessions ranked so that pick = 1 is the one to report, with
   the values the export works out from the session dates. */
latest AS (
    SELECT
        x.*,
        COUNT(*) OVER (PARTITION BY x.student_id)                               AS SESSION_COUNT,
        COUNT(sm.stuload_id) OVER (PARTITION BY x.student_id)                   AS SESSIONS_WITH_MODULES,
        COALESCE(sm.module_count, 0)                                            AS MODULE_COUNT,
        sm.module_list                                                          AS MODULES,
        sm.latest_plan_id,
        ROW_NUMBER() OVER (PARTITION BY x.student_id
                           ORDER BY (sm.stuload_id IS NOT NULL) DESC,
                                    x.period_start DESC,
                                    x.crel_active DESC, x.stuload_id DESC)      AS pick,

        NULLIF(CASE
            WHEN x.ENGENDDATE IS NOT NULL AND x.ENGENDDATE > x.period_start
                 AND x.ENGENDDATE < x.period_end AND x.ENGENDDATE < x.instance_end THEN x.ENGENDDATE
            WHEN x.hesa_end = '' AND x.instance_end <> '' AND x.instance_end < x.today THEN x.instance_end
            ELSE x.hesa_end
        END, '')                                                                AS SCSENDDATE,

        COALESCE(x.fundcomp_set,
            CASE
                WHEN x.period_end <> '' AND x.period_end < x.today THEN '01'
                WHEN x.period_start <> '' AND x.period_start <= x.today
                     AND x.period_end <> '' AND x.period_end > x.today THEN '03'
                ELSE '02'
            END)                                                                AS FUNDCOMP,

        /* Reason the session ended: the value set on the session, otherwise
           worked out from the session dates (4 = completed, 2 = ended early). */
        COALESCE(x.rsnscsend_set,
            CASE
                WHEN (x.hesa_end = '' AND x.instance_end <= x.today)
                  OR (x.hesa_end <> '' AND x.hesa_end = x.instance_end)
                  OR (x.hesa_end <> '' AND x.hesa_end > x.instance_end AND x.instance_end <= x.today) THEN 4
                WHEN x.hesa_end <> '' AND x.hesa_end > x.instance_start AND x.hesa_end < x.instance_end THEN 2
            END)                                                                AS rsnscsend_id
    FROM sess x
    LEFT JOIN session_modules sm ON sm.stuload_id = x.stuload_id
)

SELECT
    x.student_id, x.crel_id, x.stuload_id, x.course_id, x.course_name,
    x.STUDENT_STATUS, x.INTAKE_SEMESTER,
    x.SESSION_COUNT, x.SESSIONS_WITH_MODULES,

    /* Student */
    x.SID, x.BIRTHDTE, x.ETHNIC, x.ETHNIC_NAME, x.FNAMES, x.GENDERID, x.GENDERID_NAME,
    x.NATION, x.NATION_NAME, x.OWNSTU, x.RELIGION, x.RELIGION_NAME, x.SEXID, x.SEXID_NAME,
    x.SEXORT, x.SEXORT_NAME, x.SSN, x.SURNAME, x.TTACCOM, x.TTACCOM_NAME, x.TTPCODE,
    x.DISABILITY, x.DISABILITY_NAME,

    /* Engagement, entry profile, leaver, qualification awarded */
    x.NUMHUS, x.ENGEXPECTEDENDDATE, x.ENGSTARTDATE, x.OWNENGID, x.FEEELIG, x.FEEELIG_NAME,
    x.CARELEAVER, x.CARELEAVER_NAME, x.PERMADDCOUNTRY, x.PERMADDCOUNTRY_NAME, x.PERMADDPOSTCODE,
    x.PREVIOUSPROVIDER, x.PREVIOUSPROVIDER_NAME, x.HIGHESTQOE, x.HIGHESTQOE_NAME,
    x.ENTRYQUALAWARDID, x.ENTRYQUALAWARDRESULT, x.ENTRYQUALAWARDRESULT_NAME,
    x.QUALTYPEID, x.QUALTYPEID_NAME, x.QUALYEAR, x.SUBJECTID, x.SUBJECTID_NAME,
    x.ENGENDDATE, x.RSNENGEND, x.RSNENGEND_NAME,
    x.QUALAWARDID, x.QUALID, x.QUALAWARDRESULT, x.QUALAWARDRESULT_NAME,

    /* Student course session (the latest one) */
    x.SCSESSIONID, x.COURSEID, x.COURSEID_NAME, x.INVOICEFEEAMOUNT, x.INVOICEHESAID,
    x.SCSENDDATE, x.SCSFEEAMOUNT, x.SCSMODE, x.SCSMODE_NAME, x.SCSSTARTDATE,
    x.SESSIONYEARID, x.YEARPRG,
    NULLIF(rsn.df_code, '')                                                     AS RSNSCSEND,
    rsn.name                                                                    AS RSNSCSEND_NAME,

    /* Funding and monitoring */
    x.ELQ, x.ELQ_NAME,
    x.FUNDCOMP,
    (SELECT l.name FROM funding_completions l
      WHERE l.df_code = x.FUNDCOMP AND l.deleted_at IS NULL ORDER BY l.active DESC, l.id LIMIT 1) AS FUNDCOMP_NAME,
    x.FUNDLENGTH,
    (SELECT l.name FROM funding_lengths l
      WHERE l.df_code = x.FUNDLENGTH AND l.deleted_at IS NULL ORDER BY l.active DESC, l.id LIMIT 1) AS FUNDLENGTH_NAME,
    x.NONREGFEE, x.NONREGFEE_NAME,

    /* Module instance (the latest of the session), then all of them */
    p.id                                                                        AS MODINSTID,
    mc.course_module_id                                                         AS MODID,
    NULLIF(cm.name, '')                                                         AS MODID_NAME,
    NULLIF(DATE_FORMAT(mtd.end_date, '%Y-%m-%d'), '0000-00-00')                 AS MODINSTENDDATE,
    NULLIF(DATE_FORMAT(mtd.start_date, '%Y-%m-%d'), '0000-00-00')               AS MODINSTSTARTDATE,
    NULLIF(mot.df_code, '')                                                     AS MODULEOUTCOME,
    mot.name                                                                    AS MODULEOUTCOME_NAME,
    NULLIF(mrs.df_code, '')                                                     AS MODULERESULT,
    mrs.name                                                                    AS MODULERESULT_NAME,
    x.MODULE_COUNT,
    x.MODULES,

    /* Reference period student load */
    x.REFPERIOD, x.`YEAR`,
    CASE WHEN x.rp_load = 99 THEN 100 WHEN x.rp_load > 0 THEN x.rp_load END     AS RPSTULOAD,

    /* Session status: "STATUSVALIDFROM = STATUSCHANGEDTO", one pair per term */
    sst.SESSIONSTATUS, sst.SESSIONSTATUS_NAME,

    x.FINSUPTYPE,

    /* Study location */
    x.STUDYLOCID, x.STUDYPROPORTION, x.VENUEID,

    /* Reference blocks at the top of the XML, as "FIELD=value" lists. A field
       that has a description shows it in brackets after its code. */
    (SELECT GROUP_CONCAT(CONCAT(f.name, IF(COALESCE(f.description, '') <> '', CONCAT(' (', f.description, ')'), ''),
                                '=', TRIM(d.field_value)) ORDER BY d.id SEPARATOR '; ')
       FROM module_datafutures d
       JOIN datafuture_fields f ON f.id = d.datafuture_field_id AND f.deleted_at IS NULL
      WHERE d.course_module_id = cm.id AND d.deleted_at IS NULL
        AND f.name <> '' AND TRIM(COALESCE(d.field_value, '')) <> '')           AS MODULE_FIELDS,
    (SELECT GROUP_CONCAT(CONCAT(f.name, IF(COALESCE(f.description, '') <> '', CONCAT(' (', f.description, ')'), ''),
                                '=', TRIM(b.field_value)) ORDER BY b.id SEPARATOR '; ')
       FROM course_base_datafutures b
       JOIN datafuture_fields f ON f.id = b.datafuture_field_id AND f.deleted_at IS NULL
      WHERE b.course_id = x.course_id AND b.deleted_at IS NULL AND f.datafuture_field_category_id = 1
        AND f.name <> '' AND TRIM(COALESCE(b.field_value, '')) <> '')           AS COURSE_FIELDS,
    (SELECT GROUP_CONCAT(CONCAT(f.name, IF(COALESCE(f.description, '') <> '', CONCAT(' (', f.description, ')'), ''),
                                '=', TRIM(b.field_value)) ORDER BY b.id SEPARATOR '; ')
       FROM course_base_datafutures b
       JOIN datafuture_fields f ON f.id = b.datafuture_field_id AND f.deleted_at IS NULL
      WHERE b.course_id = x.course_id AND b.deleted_at IS NULL AND f.datafuture_field_category_id = 2
        AND f.name NOT IN ('', 'QUALAWARDID') AND TRIM(COALESCE(b.field_value, '')) <> '') AS QUALIFICATION_FIELDS

FROM latest x
LEFT JOIN reason_for_ending_course_sessions rsn ON rsn.id = x.rsnscsend_id AND rsn.deleted_at IS NULL
LEFT JOIN session_status sst   ON sst.stuload_id = x.stuload_id
LEFT JOIN plans p              ON p.id = x.latest_plan_id
LEFT JOIN module_creations mc  ON mc.id = p.module_creation_id AND mc.deleted_at IS NULL
LEFT JOIN course_modules cm    ON cm.id = mc.course_module_id AND cm.deleted_at IS NULL
LEFT JOIN term_declarations mtd ON mtd.id = p.term_declaration_id AND mtd.deleted_at IS NULL
LEFT JOIN student_module_instance_datafutures mid
       ON mid.id = (SELECT MIN(z.id) FROM student_module_instance_datafutures z
                     WHERE z.student_id = x.student_id AND z.student_course_relation_id = x.crel_id
                       AND z.student_stuload_information_id = x.stuload_id
                       AND z.instance_term_id = p.instance_term_id
                       AND z.course_module_id = mc.course_module_id AND z.deleted_at IS NULL)
LEFT JOIN module_outcomes mot  ON mot.id = mid.MODULEOUTCOME AND mot.deleted_at IS NULL
LEFT JOIN module_results mrs   ON mrs.id = mid.MODULERESULT AND mrs.deleted_at IS NULL

WHERE x.pick = 1
ORDER BY x.OWNSTU
SQL;

    /** The 2025-26 students, by registration number: 2,264 of them. */
    private const STUDENTS = <<<'LIST'
LCC20221263 LCC20221313 LCC20221359 LCC20221463 LCC20240015 LCC20221582 LCC20230624 LCC20240043 LCC20240123
LCC20240048 LCC20240077 LCC20240567 LCC20230690 LCC20230006 LCC20240253 LCC20240448 LCC20240214 LCC20230445
LCC20230192 LCC20230154 LCC20230209 LCC20230198 LCC20230214 LCC20240207 LCC20230260 LCC20230235 LCC20230684
LCC20230659 LCC20230390 LCC20240093 LCC20240590 LCC20240405 LCC20240162 LCC20240347 LCC20240454 LCC20240130
LCC20240094 LCC20240139 LCC20240219 LCC20240447 LCC20230683 LCC20230389 LCC20240591 LCC20230440 LCC20240436
LCC20240325 LCC20240329 LCC20240150 LCC20240307 LCC20230454 LCC20240101 LCC20230560 LCC20240200 LCC20240034
LCC20240186 LCC20230496 LCC20230464 LCC20240302 LCC20230481 LCC20230600 LCC20230679 LCC20230515 LCC20230495
LCC20240124 LCC20240035 LCC20230517 LCC20240476 LCC20240461 LCC20230538 LCC20230548 LCC20230577 LCC20230598
LCC20230565 LCC20230584 LCC20240371 LCC20230629 LCC20230651 LCC20240036 LCC20230680 LCC20240151 LCC20230638
LCC20230644 LCC20230617 LCC20230646 LCC20230689 LCC20230649 LCC20230650 LCC20240397 LCC20240393 LCC20240005
LCC20240021 LCC20240002 LCC20240004 LCC20240003 LCC20240020 LCC20240013 LCC20240014 LCC20240029 LCC20240044
LCC20240247 LCC20240127 LCC20240011 LCC20240007 LCC20240010 LCC20240009 LCC20240008 LCC20240016 LCC20240204
LCC20240006 LCC20240065 LCC20240066 LCC20240156 LCC20240102 LCC20240467 LCC20240122 LCC20240018 LCC20240025
LCC20240023 LCC20240017 LCC20240019 LCC20240031 LCC20240033 LCC20240149 LCC20240143 LCC20240089 LCC20240026
LCC20240045 LCC20240024 LCC20240022 LCC20240057 LCC20240041 LCC20240082 LCC20240085 LCC20240078 LCC20240054
LCC20240046 LCC20240070 LCC20240415 LCC20240032 LCC20240028 LCC20240079 LCC20240038 LCC20240058 LCC20240084
LCC20240063 LCC20240055 LCC20240366 LCC20240049 LCC20240328 LCC20240117 LCC20240053 LCC20240039 LCC20240154
LCC20240052 LCC20240205 LCC20240081 LCC20240087 LCC20240076 LCC20240060 LCC20240047 LCC20240051 LCC20240069
LCC20240067 LCC20240074 LCC20240059 LCC20240062 LCC20240061 LCC20240080 LCC20240103 LCC20240104 LCC20240071
LCC20240119 LCC20240097 LCC20240083 LCC20240075 LCC20240252 LCC20240100 LCC20240305 LCC20240098 LCC20240231
LCC20240095 LCC20240086 LCC20240092 LCC20240096 LCC20240108 LCC20240235 LCC20240500 LCC20240142 LCC20240589
LCC20240109 LCC20240116 LCC20240110 LCC20240111 LCC20240121 LCC20240212 LCC20240136 LCC20240128 LCC20240147
LCC20240112 LCC20240141 LCC20240113 LCC20240126 LCC20240134 LCC20240161 LCC20240118 LCC20240236 LCC20240135
LCC20240137 LCC20240138 LCC20240153 LCC20240131 LCC20240148 LCC20240144 LCC20240157 LCC20240155 LCC20240163
LCC20240168 LCC20240220 LCC20240331 LCC20240309 LCC20240221 LCC20240501 LCC20240464 LCC20240189 LCC20240492
LCC20240453 LCC20240199 LCC20240266 LCC20240351 LCC20240332 LCC20240215 LCC20240243 LCC20240288 LCC20240246
LCC20240367 LCC20240360 LCC20240353 LCC20240203 LCC20240232 LCC20240356 LCC20240354 LCC20240218 LCC20240197
LCC20240195 LCC20240271 LCC20240178 LCC20240295 LCC20240316 LCC20240176 LCC20240198 LCC20240291 LCC20240208
LCC20240185 LCC20240323 LCC20240310 LCC20240172 LCC20240269 LCC20240187 LCC20240510 LCC20240282 LCC20240241
LCC20240194 LCC20240201 LCC20240242 LCC20240456 LCC20240182 LCC20240324 LCC20240267 LCC20240594 LCC20240261
LCC20240285 LCC20240245 LCC20240244 LCC20240240 LCC20240223 LCC20240229 LCC20240444 LCC20240213 LCC20240227
LCC20240174 LCC20240441 LCC20240191 LCC20240279 LCC20240209 LCC20240179 LCC20240463 LCC20240230 LCC20240369
LCC20240169 LCC20240452 LCC20240192 LCC20240234 LCC20240180 LCC20240190 LCC20240375 LCC20240251 LCC20240175
LCC20240327 LCC20240202 LCC20240183 LCC20240210 LCC20240188 LCC20240451 LCC20240193 LCC20240250 LCC20240583
LCC20240272 LCC20240313 LCC20240586 LCC20240211 LCC20240239 LCC20240274 LCC20240265 LCC20240248 LCC20240233
LCC20240379 LCC20240260 LCC20240427 LCC20240216 LCC20240349 LCC20240348 LCC20240304 LCC20240278 LCC20240345
LCC20240370 LCC20240318 LCC20240259 LCC20240342 LCC20240294 LCC20240389 LCC20240394 LCC20240308 LCC20240255
LCC20240400 LCC20240287 LCC20240357 LCC20240387 LCC20240385 LCC20240256 LCC20240277 LCC20240406 LCC20240368
LCC20240268 LCC20240335 LCC20240336 LCC20240333 LCC20240585 LCC20240358 LCC20240361 LCC20240254 LCC20240442
LCC20240364 LCC20240340 LCC20240341 LCC20240355 LCC20240258 LCC20240257 LCC20240424 LCC20240482 LCC20240297
LCC20240319 LCC20240376 LCC20240330 LCC20240315 LCC20240298 LCC20240311 LCC20240320 LCC20240314 LCC20240378
LCC20240383 LCC20240350 LCC20240292 LCC20240418 LCC20240404 LCC20240506 LCC20240403 LCC20240321 LCC20240396
LCC20240363 LCC20240440 LCC20240412 LCC20240432 LCC20240569 LCC20240290 LCC20240293 LCC20240428 LCC20240574
LCC20240481 LCC20240455 LCC20240471 LCC20240408 LCC20240286 LCC20240422 LCC20240303 LCC20240343 LCC20240338
LCC20240289 LCC20240523 LCC20240372 LCC20240517 LCC20240337 LCC20240344 LCC20240434 LCC20240548 LCC20240413
LCC20240373 LCC20240411 LCC20240431 LCC20240480 LCC20240581 LCC20240582 LCC20240407 LCC20240537 LCC20240535
LCC20240460 LCC20240542 LCC20240414 LCC20240433 LCC20240421 LCC20240522 LCC20240380 LCC20240419 LCC20240474
LCC20240551 LCC20240468 LCC20240392 LCC20240359 LCC20240390 LCC20240587 LCC20240425 LCC20240443 LCC20240503
LCC20240573 LCC20240457 LCC20240477 LCC20240478 LCC20240401 LCC20240462 LCC20240547 LCC20240508 LCC20240546
LCC20240472 LCC20240470 LCC20240543 LCC20240429 LCC20240539 LCC20240545 LCC20240544 LCC20240555 LCC20240445
LCC20240473 LCC20240578 LCC20240435 LCC20240430 LCC20240465 LCC20240505 LCC20240495 LCC20240420 LCC20240553
LCC20240524 LCC20240525 LCC20240550 LCC20240479 LCC20240575 LCC20240557 LCC20240560 LCC20240515 LCC20240504
LCC20240499 LCC20240559 LCC20240520 LCC20240502 LCC20240512 LCC20240497 LCC20240483 LCC20240490 LCC20240571
LCC20240549 LCC20240577 LCC20240526 LCC20240487 LCC20240584 LCC20240513 LCC20240534 LCC20240498 LCC20240541
LCC20240540 LCC20240496 LCC20240494 LCC20240530 LCC20240488 LCC20240563 LCC20240518 LCC20240486 LCC20240570
LCC20240527 LCC20240491 LCC20240562 LCC20240579 LCC20240532 LCC20240558 LCC20240565 LCC20240566 LCC20240538
LCC20240528 LCC20240556 LCC20240595 LCC20240596 LCC20240597 LCC20240598 LCC20240599 LCC20240600 LCC20240601
LCC20240602 LCC20240603 LCC20240604 LCC20240605 LCC20240606 LCC20240607 LCC20240608 LCC20240609 LCC20240610
LCC20240611 LCC20240612 LCC20240613 LCC20240614 LCC20240615 LCC20240616 LCC20240617 LCC20240618 LCC20240619
LCC20240620 LCC20240621 LCC20240622 LCC20240623 LCC20240624 LCC20240625 LCC20240626 LCC20240627 LCC20240628
LCC20240629 LCC20240630 LCC20240631 LCC20240632 LCC20240633 LCC20240634 LCC20240635 LCC20240637 LCC20240638
LCC20240639 LCC20240640 LCC20240641 LCC20240642 LCC20240643 LCC20240644 LCC20240646 LCC20240647 LCC20240648
LCC20240649 LCC20240650 LCC20240651 LCC20240652 LCC20240653 LCC20240654 LCC20240655 LCC20240657 LCC20240658
LCC20240659 LCC20240660 LCC20240661 LCC20240662 LCC20240663 LCC20240664 LCC20240665 LCC20240666 LCC20240667
LCC20240668 LCC20240669 LCC20240670 LCC20240671 LCC20240672 LCC20240674 LCC20240676 LCC20240677 LCC20240678
LCC20240679 LCC20240680 LCC20240681 LCC20240682 LCC20240683 LCC20240684 LCC20240685 LCC20240686 LCC20240687
LCC20240688 LCC20240691 LCC20240692 LCC20240693 LCC20240694 LCC20240695 LCC20240696 LCC20240697 LCC20240698
LCC20240699 LCC20240700 LCC20240701 LCC20240702 LCC20240703 LCC20240704 LCC20240706 LCC20240707 LCC20240709
LCC20240710 LCC20240711 LCC20240712 LCC20240714 LCC20240716 LCC20240718 LCC20240719 LCC20240720 LCC20240721
LCC20240722 LCC20240723 LCC20240725 LCC20240726 LCC20240727 LCC20240728 LCC20240729 LCC20240730 LCC20240731
LCC20240733 LCC20240734 LCC20240735 LCC20240736 LCC20240738 LCC20240739 LCC20240740 LCC20240741 LCC20240742
LCC20240743 LCC20240745 LCC20240748 LCC20240749 LCC20240751 LCC20240752 LCC20240753 LCC20240754 LCC20240755
LCC20240756 LCC20240757 LCC20240758 LCC20240759 LCC20240760 LCC20240761 LCC20240762 LCC20240763 LCC20240764
LCC20240765 LCC20240766 LCC20240767 LCC20240768 LCC20240769 LCC20240770 LCC20240771 LCC20240772 LCC20240773
LCC20240775 LCC20240776 LCC20240777 LCC20240778 LCC20240780 LCC20240781 LCC20240782 LCC20240783 LCC20240784
LCC20240785 LCC20240786 LCC20240787 LCC20240788 LCC20240789 LCC20240790 LCC20240791 LCC20240792 LCC20240793
LCC20240794 LCC20240795 LCC20240796 LCC20240797 LCC20240798 LCC20240799 LCC20240800 LCC20240801 LCC20240802
LCC20240805 LCC20240806 LCC20240807 LCC20240808 LCC20240809 LCC20240810 LCC20240812 LCC20240814 LCC20240815
LCC20240816 LCC20240818 LCC20240819 LCC20240820 LCC20240821 LCC20240822 LCC20240823 LCC20240824 LCC20240825
LCC20240826 LCC20240827 LCC20240829 LCC20240830 LCC20240832 LCC20240833 LCC20240834 LCC20240835 LCC20240836
LCC20240837 LCC20240839 LCC20240840 LCC20240842 LCC20240844 LCC20240845 LCC20240846 LCC20240847 LCC20240848
LCC20240849 LCC20240850 LCC20240851 LCC20240852 LCC20240854 LCC20240855 LCC20240856 LCC20240858 LCC20240859
LCC20240860 LCC20240861 LCC20240862 LCC20240865 LCC20240866 LCC20240867 LCC20240868 LCC20240869 LCC20240870
LCC20240871 LCC20240872 LCC20240873 LCC20240874 LCC20240875 LCC20240876 LCC20240877 LCC20240878 LCC20240880
LCC20240881 LCC20240882 LCC20240883 LCC20240885 LCC20240886 LCC20240887 LCC20240888 LCC20240889 LCC20240890
LCC20240891 LCC20240892 LCC20240893 LCC20240894 LCC20240895 LCC20240896 LCC20240897 LCC20240899 LCC20240900
LCC20240901 LCC20240902 LCC20240903 LCC20240904 LCC20240907 LCC20240908 LCC20240909 LCC20240910 LCC20240911
LCC20240912 LCC20240913 LCC20240914 LCC20240915 LCC20240916 LCC20240917 LCC20240918 LCC20240919 LCC20240920
LCC20240921 LCC20240922 LCC20240923 LCC20250002 LCC20250003 LCC20250004 LCC20250005 LCC20250006 LCC20250007
LCC20250008 LCC20250009 LCC20250010 LCC20250011 LCC20250013 LCC20250015 LCC20250016 LCC20250017 LCC20250019
LCC20250020 LCC20250021 LCC20250022 LCC20250023 LCC20250024 LCC20250025 LCC20250026 LCC20250027 LCC20250029
LCC20250031 LCC20250032 LCC20250033 LCC20250034 LCC20250035 LCC20250036 LCC20250037 LCC20250038 LCC20250039
LCC20250041 LCC20250042 LCC20250043 LCC20250044 LCC20250045 LCC20250046 LCC20250047 LCC20250048 LCC20250049
LCC20250050 LCC20250051 LCC20250052 LCC20250053 LCC20250054 LCC20250055 LCC20250057 LCC20250058 LCC20250059
LCC20250060 LCC20250061 LCC20250062 LCC20250063 LCC20250064 LCC20250065 LCC20250066 LCC20250067 LCC20250068
LCC20250069 LCC20250070 LCC20250071 LCC20240925 LCC20250072 LCC20250073 LCC20250074 LCC20250075 LCC20250077
LCC20250078 LCC20250080 LCC20250081 LCC20250082 LCC20250083 LCC20250084 LCC20250085 LCC20240926 LCC20250086
LCC20250087 LCC20250088 LCC20250091 LCC20250093 LCC20250094 LCC20250095 LCC20250097 LCC20250098 LCC20250099
LCC20250100 LCC20250101 LCC20250102 LCC20250103 LCC20250104 LCC20250106 LCC20250107 LCC20250108 LCC20250109
LCC20250110 LCC20250111 LCC20250113 LCC20250114 LCC20250115 LCC20250116 LCC20250117 LCC20250118 LCC20250119
LCC20250120 LCC20240927 LCC20250121 LCC20250122 LCC20250123 LCC20250124 LCC20250125 LCC20250126 LCC20250127
LCC20250128 LCC20250130 LCC20250131 LCC20250132 LCC20250133 LCC20250134 LCC20250136 LCC20250137 LCC20250138
LCC20250139 LCC20250140 LCC20250142 LCC20250143 LCC20250144 LCC20250145 LCC20250146 LCC20250147 LCC20250148
LCC20250149 LCC20250150 LCC20250151 LCC20250152 LCC20250153 LCC20250154 LCC20250155 LCC20250156 LCC20250157
LCC20250158 LCC20250159 LCC20250160 LCC20250161 LCC20250162 LCC20250163 LCC20250164 LCC20250165 LCC20250166
LCC20250167 LCC20250168 LCC20250169 LCC20250170 LCC20250171 LCC20250172 LCC20250173 LCC20250174 LCC20250175
LCC20250176 LCC20250177 LCC20250178 LCC20250179 LCC20250180 LCC20250181 LCC20250182 LCC20250183 LCC20250184
LCC20250185 LCC20250186 LCC20250187 LCC20250188 LCC20250189 LCC20250190 LCC20250191 LCC20250192 LCC20250193
LCC20250194 LCC20250195 LCC20250196 LCC20250198 LCC20250199 LCC20250200 LCC20250201 LCC20250202 LCC20250203
LCC20250205 LCC20250206 LCC20250208 LCC20250209 LCC20250210 LCC20250211 LCC20250212 LCC20250213 LCC20250214
LCC20250215 LCC20250216 LCC20250217 LCC20250218 LCC20250219 LCC20250220 LCC20250221 LCC20250222 LCC20250223
LCC20250224 LCC20250225 LCC20250226 LCC20250227 LCC20240929 LCC20240930 LCC20250228 LCC20250229 LCC20250230
LCC20240932 LCC20250232 LCC20250233 LCC20250234 LCC20250235 LCC20250236 LCC20250237 LCC20250238 LCC20250239
LCC20250240 LCC20250241 LCC20250242 LCC20250243 LCC20250244 LCC20250245 LCC20250246 LCC20250247 LCC20250249
LCC20250250 LCC20250251 LCC20250252 LCC20250253 LCC20250254 LCC20250255 LCC20250256 LCC20250257 LCC20250258
LCC20250259 LCC20250260 LCC20250261 LCC20250262 LCC20250263 LCC20250264 LCC20250265 LCC20250266 LCC20250267
LCC20250268 LCC20250269 LCC20250270 LCC20250272 LCC20250273 LCC20250274 LCC20250275 LCC20250276 LCC20250277
LCC20250279 LCC20250280 LCC20250281 LCC20250283 LCC20250284 LCC20250285 LCC20250286 LCC20250287 LCC20250289
LCC20250290 LCC20250291 LCC20250292 LCC20250293 LCC20250295 LCC20250296 LCC20250298 LCC20250299 LCC20250300
LCC20250301 LCC20250302 LCC20250303 LCC20250304 LCC20250305 LCC20250306 LCC20250307 LCC20250308 LCC20250309
LCC20250310 LCC20250311 LCC20250312 LCC20250313 LCC20250314 LCC20250315 LCC20250316 LCC20250317 LCC20250318
LCC20250319 LCC20250320 LCC20250321 LCC20250322 LCC20250323 LCC20250324 LCC20250325 LCC20250326 LCC20250327
LCC20250329 LCC20250330 LCC20250331 LCC20250332 LCC20250333 LCC20250334 LCC20250335 LCC20250336 LCC20250337
LCC20250338 LCC20250339 LCC20250340 LCC20250341 LCC20250342 LCC20250343 LCC20250344 LCC20250345 LCC20250346
LCC20250347 LCC20250348 LCC20250349 LCC20250350 LCC20250351 LCC20250352 LCC20250354 LCC20250356 LCC20250357
LCC20250358 LCC20250359 LCC20250360 LCC20250361 LCC20250362 LCC20250363 LCC20250365 LCC20250367 LCC20250368
LCC20250369 LCC20250370 LCC20250371 LCC20250372 LCC20250373 LCC20250374 LCC20250375 LCC20250376 LCC20250377
LCC20250378 LCC20250379 LCC20250380 LCC20250381 LCC20250382 LCC20250383 LCC20250384 LCC20250385 LCC20250386
LCC20250387 LCC20250388 LCC20250389 LCC20250390 LCC20250391 LCC20250392 LCC20250393 LCC20250394 LCC20250395
LCC20250396 LCC20250397 LCC20250398 LCC20250399 LCC20250400 LCC20250401 LCC20250402 LCC20250403 LCC20250405
LCC20250406 LCC20250407 LCC20250408 LCC20250409 LCC20250410 LCC20250411 LCC20250412 LCC20250413 LCC20250414
LCC20250415 LCC20250417 LCC20250418 LCC20250420 LCC20250421 LCC20250422 LCC20250423 LCC20250424 LCC20250425
LCC20250426 LCC20250427 LCC20250428 LCC20250429 LCC20250430 LCC20250431 LCC20250432 LCC20250433 LCC20250434
LCC20250435 LCC20250436 LCC20250437 LCC20250438 LCC20250439 LCC20250440 LCC20250441 LCC20250442 LCC20250443
LCC20250444 LCC20250445 LCC20250446 LCC20250447 LCC20250448 LCC20250449 LCC20250450 LCC20250451 LCC20250452
LCC20250453 LCC20250454 LCC20250455 LCC20250456 LCC20250457 LCC20250458 LCC20250459 LCC20250460 LCC20250461
LCC20250462 LCC20250463 LCC20250464 LCC20250465 LCC20250466 LCC20250467 LCC20250468 LCC20250469 LCC20250470
LCC20250471 LCC20250472 LCC20250473 LCC20250475 LCC20250477 LCC20250478 LCC20250479 LCC20250481 LCC20250482
LCC20250483 LCC20250484 LCC20250485 LCC20250487 LCC20250488 LCC20250489 LCC20250490 LCC20250491 LCC20250492
LCC20250493 LCC20250494 LCC20250495 LCC20250496 LCC20250498 LCC20250499 LCC20250500 LCC20250501 LCC20250502
LCC20250503 LCC20250504 LCC20250505 LCC20250506 LCC20250507 LCC20250508 LCC20250509 LCC20250510 LCC20250511
LCC20250512 LCC20250513 LCC20250514 LCC20250515 LCC20250516 LCC20250517 LCC20250518 LCC20250519 LCC20250520
LCC20250521 LCC20250522 LCC20250523 LCC20250524 LCC20250525 LCC20250526 LCC20250527 LCC20250528 LCC20250529
LCC20250530 LCC20250532 LCC20250533 LCC20250534 LCC20250536 LCC20250537 LCC20250538 LCC20250539 LCC20250540
LCC20250541 LCC20250542 LCC20250543 LCC20250544 LCC20250545 LCC20250546 LCC20250547 LCC20250548 LCC20250550
LCC20250551 LCC20250552 LCC20250553 LCC20250554 LCC20250555 LCC20250556 LCC20250557 LCC20250558 LCC20250559
LCC20250560 LCC20250561 LCC20250562 LCC20250563 LCC20250565 LCC20250566 LCC20250567 LCC20250568 LCC20250570
LCC20250571 LCC20250572 LCC20250573 LCC20250574 LCC20250576 LCC20250577 LCC20250578 LCC20250579 LCC20250580
LCC20250581 LCC20250582 LCC20250583 LCC20250584 LCC20250585 LCC20250586 LCC20250587 LCC20250588 LCC20250590
LCC20250591 LCC20250592 LCC20250593 LCC20250595 LCC20250596 LCC20250597 LCC20250598 LCC20250599 LCC20250600
LCC20250601 LCC20250602 LCC20250603 LCC20250604 LCC20250605 LCC20250606 LCC20250607 LCC20250609 LCC20250610
LCC20250611 LCC20250612 LCC20250613 LCC20250614 LCC20250615 LCC20250616 LCC20250617 LCC20250618 LCC20250619
LCC20250620 LCC20250621 LCC20250622 LCC20250623 LCC20250624 LCC20250625 LCC20250627 LCC20250628 LCC20250629
LCC20250630 LCC20250631 LCC20250632 LCC20250633 LCC20250634 LCC20250635 LCC20250636 LCC20250637 LCC20250638
LCC20250639 LCC20250640 LCC20250641 LCC20250642 LCC20250643 LCC20250644 LCC20250645 LCC20250646 LCC20250647
LCC20250648 LCC20250649 LCC20250650 LCC20250651 LCC20250652 LCC20250653 LCC20250654 LCC20250655 LCC20250656
LCC20250657 LCC20250658 LCC20250659 LCC20250660 LCC20250661 LCC20250662 LCC20250663 LCC20250664 LCC20250665
LCC20250666 LCC20250667 LCC20250671 LCC20250672 LCC20250673 LCC20250675 LCC20250677 LCC20250678 LCC20250679
LCC20250680 LCC20250681 LCC20250682 LCC20250683 LCC20250684 LCC20250685 LCC20250686 LCC20250687 LCC20250688
LCC20250689 LCC20250690 LCC20250691 LCC20250692 LCC20250693 LCC20250695 LCC20250696 LCC20250698 LCC20250699
LCC20250700 LCC20250701 LCC20250702 LCC20250705 LCC20250706 LCC20250707 LCC20250708 LCC20250709 LCC20250711
LCC20250712 LCC20250713 LCC20250714 LCC20250715 LCC20250716 LCC20250717 LCC20250718 LCC20250719 LCC20250720
LCC20250721 LCC20250722 LCC20250723 LCC20250724 LCC20250725 LCC20250726 LCC20250727 LCC20250729 LCC20250730
LCC20250731 LCC20250732 LCC20250734 LCC20250735 LCC20250736 LCC20250737 LCC20250738 LCC20250739 LCC20250740
LCC20260001 LCC20260002 LCC20260003 LCC20260004 LCC20260007 LCC20260008 LCC20260009 LCC20260010 LCC20260011
LCC20260012 LCC20260013 LCC20260014 LCC20260015 LCC20260016 LCC20260017 LCC20260018 LCC20260019 LCC20260020
LCC20260021 LCC20260022 LCC20260023 LCC20260024 LCC20260026 LCC20260027 LCC20260028 LCC20260029 LCC20260030
LCC20260031 LCC20260032 LCC20260033 LCC20260034 LCC20260035 LCC20260036 LCC20260037 LCC20260038 LCC20260039
LCC20260040 LCC20260041 LCC20260042 LCC20260043 LCC20260044 LCC20260045 LCC20260046 LCC20260047 LCC20260048
LCC20260049 LCC20260050 LCC20260051 LCC20260052 LCC20260053 LCC20260054 LCC20260055 LCC20260056 LCC20260057
LCC20260058 LCC20260059 LCC20260060 LCC20260061 LCC20260062 LCC20260063 LCC20260065 LCC20260068 LCC20260069
LCC20260070 LCC20260071 LCC20260072 LCC20260073 LCC20260075 LCC20260077 LCC20260078 LCC20260079 LCC20260080
LCC20260081 LCC20260082 LCC20260083 LCC20260084 LCC20260085 LCC20260086 LCC20260087 LCC20260088 LCC20260089
LCC20260090 LCC20260091 LCC20260092 LCC20260093 LCC20260094 LCC20260095 LCC20260096 LCC20260097 LCC20260098
LCC20260099 LCC20260100 LCC20260101 LCC20260103 LCC20260104 LCC20260105 LCC20260106 LCC20260107 LCC20260108
LCC20260110 LCC20260111 LCC20260112 LCC20260113 LCC20260114 LCC20260116 LCC20260117 LCC20260118 LCC20260119
LCC20260120 LCC20260121 LCC20260122 LCC20260123 LCC20260124 LCC20260125 LCC20260126 LCC20260127 LCC20260128
LCC20260129 LCC20260130 LCC20260131 LCC20260132 LCC20260133 LCC20260134 LCC20260135 LCC20260136 LCC20260137
LCC20260138 LCC20260139 LCC20260141 LCC20260142 LCC20260143 LCC20260144 LCC20260145 LCC20260146 LCC20260147
LCC20260148 LCC20260149 LCC20260150 LCC20260151 LCC20260152 LCC20260154 LCC20260156 LCC20260158 LCC20260159
LCC20260160 LCC20260161 LCC20260162 LCC20260163 LCC20260164 LCC20260165 LCC20260166 LCC20260167 LCC20260168
LCC20260171 LCC20260172 LCC20260173 LCC20260174 LCC20260175 LCC20260176 LCC20260177 LCC20260178 LCC20260179
LCC20260180 LCC20260181 LCC20260182 LCC20260183 LCC20260185 LCC20260188 LCC20260189 LCC20250741 LCC20260190
LCC20260191 LCC20260192 LCC20260193 LCC20260194 LCC20260195 LCC20260196 LCC20260197 LCC20260198 LCC20260199
LCC20260200 LCC20260201 LCC20260202 LCC20250743 LCC20250744 LCC20260203 LCC20260204 LCC20260205 LCC20260206
LCC20260207 LCC20260208 LCC20260209 LCC20260210 LCC20260211 LCC20260212 LCC20250745 LCC20260213 LCC20260214
LCC20260215 LCC20260216 LCC20260217 LCC20260218 LCC20260219 LCC20260220 LCC20260221 LCC20260222 LCC20260223
LCC20260224 LCC20260226 LCC20260228 LCC20260230 LCC20260231 LCC20260232 LCC20260235 LCC20260237 LCC20260238
LCC20260239 LCC20260240 LCC20260242 LCC20260244 LCC20260245 LCC20260246 LCC20260247 LCC20260248 LCC20260249
LCC20260250 LCC20260251 LCC20260252 LCC20260253 LCC20260254 LCC20260255 LCC20260257 LCC20260258 LCC20260260
LCC20260261 LCC20260263 LCC20260265 LCC20260267 LCC20260268 LCC20260269 LCC20260270 LCC20260271 LCC20260272
LCC20260273 LCC20260274 LCC20260275 LCC20260276 LCC20260277 LCC20260278 LCC20260279 LCC20260280 LCC20260281
LCC20260282 LCC20260283 LCC20260284 LCC20260285 LCC20260286 LCC20260287 LCC20260288 LCC20260289 LCC20260290
LCC20260291 LCC20260292 LCC20260293 LCC20260294 LCC20260295 LCC20260296 LCC20260297 LCC20260298 LCC20260299
LCC20260300 LCC20260301 LCC20260302 LCC20260303 LCC20260304 LCC20260305 LCC20260306 LCC20260307 LCC20260308
LCC20260309 LCC20260310 LCC20260311 LCC20260312 LCC20260313 LCC20260314 LCC20260315 LCC20260316 LCC20260317
LCC20260319 LCC20260320 LCC20260321 LCC20260323 LCC20260324 LCC20260325 LCC20260327 LCC20260328 LCC20260329
LCC20260330 LCC20260331 LCC20260332 LCC20260335 LCC20260336 LCC20260337 LCC20260338 LCC20260339 LCC20260340
LCC20260341 LCC20260342 LCC20260343 LCC20260344 LCC20260345 LCC20260346 LCC20260347 LCC20260348 LCC20260349
LCC20260350 LCC20260351 LCC20260352 LCC20260353 LCC20260354 LCC20260355 LCC20260357 LCC20260358 LCC20260359
LCC20260360 LCC20260361 LCC20260362 LCC20260363 LCC20260364 LCC20260365 LCC20260366 LCC20260367 LCC20260369
LCC20260370 LCC20260371 LCC20260372 LCC20260373 LCC20260375 LCC20260376 LCC20260377 LCC20260378 LCC20260379
LCC20260381 LCC20260382 LCC20260383 LCC20260384 LCC20260385 LCC20260386 LCC20260387 LCC20260388 LCC20260389
LCC20260390 LCC20260391 LCC20260392 LCC20260393 LCC20260394 LCC20260395 LCC20260396 LCC20260397 LCC20260398
LCC20260399 LCC20260400 LCC20260401 LCC20260402 LCC20260403 LCC20260404 LCC20260405 LCC20260406 LCC20260407
LCC20260408 LCC20260409 LCC20260410 LCC20260411 LCC20260412 LCC20260413 LCC20260414 LCC20260415 LCC20260416
LCC20260417 LCC20260418 LCC20260419 LCC20260420 LCC20260421 LCC20260422 LCC20260423 LCC20260424 LCC20260425
LCC20260426 LCC20260427 LCC20260428 LCC20260429 LCC20260430 LCC20260431 LCC20260432 LCC20260433 LCC20260434
LCC20260435 LCC20260436 LCC20260437 LCC20260438 LCC20260439 LCC20260440 LCC20260441 LCC20260442 LCC20260443
LCC20260444 LCC20260445 LCC20260446 LCC20260447 LCC20260448 LCC20260449 LCC20260450 LCC20260452 LCC20260453
LCC20260454 LCC20260455 LCC20260456 LCC20260458 LCC20260459 LCC20260460 LCC20260461 LCC20260462 LCC20260463
LCC20260464 LCC20260465 LCC20260466 LCC20260467 LCC20260468 LCC20260469 LCC20260470 LCC20260471 LCC20260472
LCC20260473 LCC20260490 LCC20260474 LCC20260475 LCC20260477 LCC20260478 LCC20260479 LCC20260480 LCC20260482
LCC20260483 LCC20260484 LCC20260485 LCC20260486 LCC20260487 LCC20260488 LCC20260489 LCC20260491 LCC20260492
LCC20260494 LCC20260495 LCC20260496 LCC20260497 LCC20260498 LCC20260499 LCC20260500 LCC20260501 LCC20260503
LCC20260504 LCC20260505 LCC20260506 LCC20260507 LCC20260508 LCC20260509 LCC20260510 LCC20260512 LCC20260514
LCC20260515 LCC20260516 LCC20260517 LCC20260518 LCC20260519 LCC20260520 LCC20260521 LCC20260522 LCC20260523
LCC20260524 LCC20260525 LCC20260526 LCC20260527 LCC20260528 LCC20260529 LCC20260530 LCC20260531 LCC20260532
LCC20260533 LCC20260534 LCC20260535 LCC20260536 LCC20260537 LCC20260538 LCC20260539 LCC20260540 LCC20260541
LCC20260542 LCC20260543 LCC20260544 LCC20260545 LCC20260546 LCC20260547 LCC20260548 LCC20260549 LCC20260552
LCC20260553 LCC20260554 LCC20260555 LCC20260556 LCC20260557 LCC20260558 LCC20260559 LCC20260560 LCC20260561
LCC20260562 LCC20260563 LCC20260564 LCC20260565 LCC20260566 LCC20260567 LCC20260569 LCC20260570 LCC20260571
LCC20260572 LCC20260573 LCC20260574 LCC20260575 LCC20260576 LCC20260577 LCC20260578 LCC20260579 LCC20260580
LCC20260581 LCC20260584 LCC20260586 LCC20260588 LCC20260589 LCC20260590 LCC20260591 LCC20260593 LCC20260594
LCC20260595 LCC20260596 LCC20260597 LCC20260598 LCC20260599 LCC20260600 LCC20260601 LCC20260602 LCC20260603
LCC20260604 LCC20260605 LCC20260606 LCC20260607 LCC20260608 LCC20260609 LCC20260610 LCC20260611 LCC20260612
LCC20260613 LCC20260614 LCC20260615 LCC20260616 LCC20260617 LCC20260618 LCC20260619 LCC20260620 LCC20260621
LCC20260622 LCC20260623 LCC20260624 LCC20260625 LCC20260626 LCC20260627 LCC20260628 LCC20260629 LCC20260630
LCC20260631 LCC20260632 LCC20260633 LCC20260634 LCC20260636 LCC20260637 LCC20260638 LCC20260640 LCC20260641
LCC20260642 LCC20260643 LCC20260644 LCC20260645 LCC20260646 LCC20260647 LCC20260648 LCC20260649 LCC20260650
LCC20260651 LCC20260652 LCC20260655 LCC20260656 LCC20260657 LCC20260658 LCC20260659 LCC20260660 LCC20260661
LCC20260662 LCC20260663 LCC20260664 LCC20260665 LCC20260666 LCC20260668 LCC20260669 LCC20260670 LCC20260671
LCC20260672 LCC20260673 LCC20260674 LCC20260675 LCC20260676 LCC20260677 LCC20260678 LCC20260679 LCC20260680
LCC20260681 LCC20260682 LCC20260683 LCC20260685 LCC20260686 LCC20260687 LCC20260688 LCC20260689 LCC20260690
LCC20260693 LCC20260694 LCC20260695 LCC20260696 LCC20260697 LCC20260698 LCC20260699 LCC20260700 LCC20260701
LCC20260702 LCC20260703 LCC20260706 LCC20260707 LCC20260709 LCC20260710 LCC20260711 LCC20260712 LCC20260713
LCC20260714 LCC20260715 LCC20260716 LCC20260717 LCC20260718 LCC20260719 LCC20260720 LCC20260721 LCC20260722
LCC20260723 LCC20260724 LCC20260725 LCC20260726 LCC20260727 LCC20260728 LCC20260729 LCC20260730 LCC20260731
LCC20260732 LCC20260733 LCC20260734 LCC20260736 LCC20260737 LCC20260738 LCC20260740 LCC20260741 LCC20260742
LCC20260743 LCC20260745 LCC20260747 LCC20260748 LCC20260749 LCC20260750 LCC20260751 LCC20260752 LCC20260754
LCC20260755 LCC20260756 LCC20260758 LCC20260759 LCC20260760 LCC20260761 LCC20260762 LCC20260763 LCC20260764
LCC20260765 LCC20260766 LCC20260767 LCC20260768 LCC20260769 LCC20260771 LCC20260772 LCC20260773 LCC20260774
LCC20260775 LCC20260776 LCC20260777 LCC20260778 LCC20260779 LCC20260780 LCC20260781 LCC20260782 LCC20260783
LCC20230570 LCC20230383 LCC20221013 LCC20230059 LCC20230130 LCC20230411 LCC20230551 LCC20230550 LCC20230675
LCC20230479 LCC20230456 LCC20230674 LCC20230461 LCC20230530 LCC20230523 LCC20230511 LCC20230533 LCC20230536
LCC20230547 LCC20230370 LCC20230182 LCC20221524 LCC20230044 LCC20230087 LCC20230052 LCC20230385 LCC20230074
LCC20230261 LCC20230158 LCC20230123 LCC20230171 LCC20230170 LCC20230206 LCC20230229 LCC20230212 LCC20230195
LCC20230223 LCC20230374 LCC20230349 LCC20230286 LCC20230357 LCC20230311 LCC20230352 LCC20230338 LCC20230412
LCC20230339 LCC20230266 LCC20230341 LCC20230433 LCC20230279 LCC20230276 LCC20230420 LCC20230284 LCC20230353
LCC20230359 LCC20230498 LCC20230503 LCC20230652 LCC20230656 LCC20230655 LCC20230520 LCC20230256 LCC20221535
LCC20230253 LCC20230391 LCC20230574 LCC20230274 LCC20230297 LCC20230448 LCC20230460 LCC20230557 LCC20230516
LCC20230350 LCC20221548 LCC20250090 LCC20230620 LCC20230615
LIST;
}
