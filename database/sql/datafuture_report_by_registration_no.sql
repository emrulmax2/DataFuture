/*
  HESA Data Futures report - the fields the XML export writes, one row per student.

  Put the registration numbers in the list marked <-- (one or many) and run.
  To list every student the export would include, delete that condition
  instead of pasting thousands of numbers. Needs MySQL 8.

  Where the export repeats a block, this takes the latest one:
    - course session : the student's latest session that the export would write
                       (one with modules), by start date. If none has modules,
                       their latest visible session. Two sessions starting on
                       the same day: the one on the active course relation.
    - engagement     : the course relation that session belongs to.
    - module         : the latest module instance of that session. MODULES lists
                       every module instance of the session as MODINSTID:MODID.
  SESSION_COUNT and SESSIONS_WITH_MODULES say how many sessions were available.

  Columns are named after the XML tags and follow the order of the XML. A coded
  field is followed by <TAG>_NAME, the name of that code in its lookup list.

  Same rules as the export: the student must have HESA status on, only stuloads
  marked visible for the report are read, and the student needs at least one of
  them on a course other than 30 / 31. There is no date range.
*/
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
      /* Delete the next five lines to list every student the export would include. */
      AND s.registration_no IN (                     -- <-- student registration numbers
            'LCC20250049',
            'LCC20250417',
            'LCC20240922'
          )
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
ORDER BY x.OWNSTU;
