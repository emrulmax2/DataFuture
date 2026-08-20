<?php

namespace App\Services;

/**
 * Ring colour for the photo on a student ID card.
 *
 * The same card is printed from the student portal (UploadController) and from
 * the Task Manager dialog (PendingTaskManagerController), so both read the
 * colour from here — otherwise one student's card comes out in two different
 * colours depending on which screen produced it.
 *
 * Colours are keyed on course id and are unique per course. The twelve
 * highest-volume courses each get their own hue family — not a lighter or
 * darker shade of a neighbour's — because a dark green next to a bright green
 * reads as the same card at arm's length.
 */
class StudentIdCardPalette
{
    private const COURSE_COLOURS = [
        /* The twelve highest-volume courses carry 96% of students and are the
           cards staff compare side by side, so each one gets its own hue
           family rather than a shade of a neighbour's. */
        27 => '#D21A22', // red      HND in Business (Entrepreneurship And Small Business Management)
        6  => '#1250D2', // blue     HND IN BUSINESS
        9  => '#6B21C9', // violet   HND IN HEALTH AND SOCIAL CARE
        5  => '#00BF63', // green    HND In Hospitality Management
        11 => '#E2640F', // orange   EXTENDED DIPLOMA IN STRATEGIC MANAGEMENT AND LEADERSHIP
        19 => '#B4930E', // gold     AABPS LEVEL 6 DIPLOMA IN BUSINESS MANAGEMENT STUDIES
        12 => '#0E93B4', // cyan     HND IN COMPUTING AND SYSTEMS DEVELOPMENT
        8  => '#6E8C0A', // olive    PGD IN BUSINESS MANAGEMENT
        23 => '#123A78', // navy     GRADUATE DIPLOMA IN BUSINESS ADMINISTRATION
        32 => '#C41B8E', // magenta  HND in Health and Social Care Practice (Social and Community Work)
        24 => '#7B4A18', // brown    FDA in Business Management
        16 => '#E0246E', // pink     BSc (Hons.) MANAGEMENT AND BUSINESS ADMINISTRATION (Top-Up)

        /* Everything below is deliberately kept out of the green and red
           families — those two read as "Hospitality" and "Business
           (Entrepreneurship)" to anyone who handles these cards. */
        30 => '#ED12E6', // BSc (Hons) Business Management Top Up Degree
        14 => '#128EED', // ACCA
        7  => '#7A295A', // HND IN COMPUTING
        22 => '#6E3091', // DIPLOMA IN MANAGEMENT
        17 => '#2E6321', // BTEC HNC in BUSINESS
        28 => '#50B40E', // HND in Healthcare Practice (Integrated Health and Social Care)
        18 => '#1212ED', // AABPS LEVEL 5 DIPLOMA IN BUSINESS MANAGEMENT STUDIES
        21 => '#7A7229', // DIPLOMA IN HEALTHCARE MANAGEMENT
        1  => '#AC2BB6', // MANAGEMENT OF INFORMATION SYSTEMS
        26 => '#BF8440', // FDA in Events and Hospitality Management
        3  => '#3870A8', // LLB (Hons)
        15 => '#207B09', // GRADUATE DIPLOMA IN BUSINESS MANAGEMENT & MARKETING
        20 => '#B312ED', // EXTENDED DIPLOMA IN LEADERSHIP AND MANAGEMENT IN THE HEALTH AND SOCIAL CARE SECTOR
        2  => '#2B59B6', // ESOL SKILLS FOR LIFE
        13 => '#DE8C21', // ACADEMIC SKILLS DEVELOPMENT
        25 => '#ED12BA', // SFA ILM Level 3 Dip in Leadership and Management
        29 => '#1245ED', // HND in Social and Community Work (Community Development)
    ];

    /**
     * Used when a course has no entry above — a new course still prints a
     * sensible card, and always the same colour for the same course.
     */
    private const FALLBACK_COLOURS = [
        '#4A2E85', '#8C5A0E', '#2E6E85', '#85306B', '#3F4A85', '#6B4A2E',
    ];

    public static function borderColour($courseId): string
    {
        $courseId = (int) $courseId;

        if (isset(self::COURSE_COLOURS[$courseId])):
            return self::COURSE_COLOURS[$courseId];
        endif;

        return self::FALLBACK_COLOURS[abs($courseId) % count(self::FALLBACK_COLOURS)];
    }
}
