<?php

namespace App\Support;

/**
 * The three levels a policy test can be set at, and the badge each one earns.
 *
 * Every question in a policy's bank belongs to exactly one level. A test at a
 * level draws a fixed mix of all three (see MIX) — never one level alone.
 * Passing a test earns the member of staff that level's badge for the policy:
 * Beginner = bronze, Intermediate = silver, Expert = gold.
 *
 * The keys below are what is stored (policy_questions.level,
 * policy_assignments.level, policy_attempts.level, policy_badges.level). The
 * colours are shared with the Blade partials and resources/js/policy-assessment/levels.js,
 * so a badge looks the same wherever it is drawn.
 */
class PolicyLevel
{
    const BEGINNER = 'beginner';
    const INTERMEDIATE = 'intermediate';
    const EXPERT = 'expert';

    const LABELS = [
        self::BEGINNER => 'Beginner',
        self::INTERMEDIATE => 'Intermediate',
        self::EXPERT => 'Expert',
    ];

    const BADGE_NAMES = [
        self::BEGINNER => 'Bronze',
        self::INTERMEDIATE => 'Silver',
        self::EXPERT => 'Gold',
    ];

    /** How many stars the medallion carries, so a badge never relies on colour alone. */
    const STARS = [
        self::BEGINNER => 1,
        self::INTERMEDIATE => 2,
        self::EXPERT => 3,
    ];

    const COLOURS = [
        self::BEGINNER => ['main' => '#b0703a', 'soft' => '#f6e6d8', 'ink' => '#7a4a22'],
        self::INTERMEDIATE => ['main' => '#7b8794', 'soft' => '#eceff3', 'ink' => '#4a5563'],
        self::EXPERT => ['main' => '#c49a12', 'soft' => '#fbf2d3', 'ink' => '#7a5d00'],
    ];

    /**
     * The question pattern of each test, as percentages of the questions drawn:
     * exam level => [question level => %]. A test is never drawn from one
     * level alone — a Beginner exam still carries some harder questions, an
     * Expert exam some easier ones. Each row adds up to 100.
     */
    const MIX = [
        self::BEGINNER => [self::BEGINNER => 70, self::INTERMEDIATE => 20, self::EXPERT => 10],
        self::INTERMEDIATE => [self::BEGINNER => 40, self::INTERMEDIATE => 40, self::EXPERT => 20],
        self::EXPERT => [self::BEGINNER => 20, self::INTERMEDIATE => 30, self::EXPERT => 50],
    ];

    /**
     * [question level => %] for a test at the given level, easiest first. An
     * unknown level gets the Beginner pattern.
     */
    public static function mix(string $examLevel): array
    {
        return (isset(self::MIX[$examLevel]) ? self::MIX[$examLevel] : self::MIX[self::BEGINNER]);
    }

    /**
     * How many questions of each level a test of $total questions draws:
     * [question level => count], easiest first, adding up to exactly $total.
     *
     * Each share is rounded down and the questions left over go to the levels
     * with the largest remainders — ties to the bigger share, then the easier
     * level — so 10 questions give 7/2/1, 4/4/2 and 2/3/5.
     */
    public static function quota(string $examLevel, int $total): array
    {
        $mix = self::mix($examLevel);
        $total = max(0, $total);

        $quota = [];
        $remainders = [];
        $position = 0;
        foreach($mix as $level => $percent):
            $exact = $total * $percent / 100;
            $quota[$level] = (int) floor($exact);
            $remainders[] = ['level' => $level, 'remainder' => $exact - floor($exact), 'percent' => $percent, 'position' => $position];
            $position += 1;
        endforeach;

        usort($remainders, function ($a, $b) {
            if(abs($a['remainder'] - $b['remainder']) > 0.000001):
                return ($a['remainder'] > $b['remainder'] ? -1 : 1);
            endif;
            if($a['percent'] != $b['percent']):
                return ($a['percent'] > $b['percent'] ? -1 : 1);
            endif;

            return $a['position'] <=> $b['position'];
        });

        $left = $total - array_sum($quota);
        foreach($remainders as $row):
            if($left <= 0):
                break;
            endif;
            $quota[$row['level']] += 1;
            $left -= 1;
        endforeach;

        return $quota;
    }

    /** The level keys, easiest first. */
    public static function all(): array
    {
        return array_keys(self::LABELS);
    }

    /** key => label, easiest first. */
    public static function labels(): array
    {
        return self::LABELS;
    }

    /** 'Beginner', 'Intermediate' or 'Expert'; an unknown level reads as ''. */
    public static function label(?string $level): string
    {
        return ($level !== null && isset(self::LABELS[$level]) ? self::LABELS[$level] : '');
    }

    public static function isValid(?string $level): bool
    {
        return ($level !== null && isset(self::LABELS[$level]));
    }

    /** 'Bronze', 'Silver' or 'Gold'; an unknown level reads as ''. */
    public static function badgeName(string $level): string
    {
        return (isset(self::BADGE_NAMES[$level]) ? self::BADGE_NAMES[$level] : '');
    }

    /** 1, 2 or 3 stars; an unknown level gets 1. */
    public static function stars(string $level): int
    {
        return (isset(self::STARS[$level]) ? self::STARS[$level] : 1);
    }

    /**
     * ['main' => …, 'soft' => …, 'ink' => …] — the medal colour, a pale tint for
     * backgrounds, and a dark shade for text on the tint. An unknown level
     * gets the Beginner colours.
     */
    public static function colours(string $level): array
    {
        return (isset(self::COLOURS[$level]) ? self::COLOURS[$level] : self::COLOURS[self::BEGINNER]);
    }
}
