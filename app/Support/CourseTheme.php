<?php

namespace App\Support;

use App\Models\Student;

class CourseTheme
{
    /**
     * Course theme palette registry keyed by courses.color_theme.
     *
     * @return array<string, array<string, string>>
     */
    public static function palettes(): array
    {
        return [
            'lcc_teal' => [
                'hero' => '#0F252D',
                'primary' => '#0B6B66',
                'primary_hover' => '#095752',
                'tint' => '#E5F2F0',
                'border' => '#BFDEDA',
            ],
            'burgundy' => [
                'hero' => '#2A1418',
                'primary' => '#8E2A3C',
                'primary_hover' => '#741F30',
                'tint' => '#F6E9EC',
                'border' => '#E4C4CC',
            ],
            'oxford_blue' => [
                'hero' => '#13203A',
                'primary' => '#2E4FA3',
                'primary_hover' => '#253F86',
                'tint' => '#E9EDF8',
                'border' => '#C6D0EC',
            ],
            'forest_green' => [
                'hero' => '#12281F',
                'primary' => '#1E6B4E',
                'primary_hover' => '#17553E',
                'tint' => '#E6F2ED',
                'border' => '#C2DFD3',
            ],
            'aubergine' => [
                'hero' => '#241430',
                'primary' => '#5E3A8C',
                'primary_hover' => '#4C2E73',
                'tint' => '#EFE9F5',
                'border' => '#D5C6E6',
            ],
            'terracotta' => [
                'hero' => '#33201A',
                'primary' => '#A8492A',
                'primary_hover' => '#8C3A1F',
                'tint' => '#F7ECE7',
                'border' => '#E8CCC0',
            ],
            'slate' => [
                'hero' => '#1B2228',
                'primary' => '#3D5966',
                'primary_hover' => '#2F4750',
                'tint' => '#EAEFF1',
                'border' => '#C9D5DA',
            ],
            'deep_petrol' => [
                'hero' => '#0E2233',
                'primary' => '#14647E',
                'primary_hover' => '#0F4F64',
                'tint' => '#EFF6F8',
                'border' => '#BFDAE2',
            ],
        ];
    }

    /**
     * Resolve a palette for the student's active/current course.
     *
     * @return array<string, string>
     */
    public static function forStudent(?Student $student): array
    {
        $palettes = static::palettes();
        $defaultKey = 'lcc_teal';
        $themeKey = optional(optional($student?->crel)->course)->color_theme ?: $defaultKey;

        return $palettes[$themeKey] ?? $palettes[$defaultKey];
    }

    public static function inlineCssVariables(?Student $student): string
    {
        $palette = static::forStudent($student);

        return implode('; ', [
            '--student-profile-header: '.$palette['hero'],
            '--student-profile-header-hover: '.static::mixHex($palette['hero'], '#FFFFFF', 0.08),
            '--student-profile-accent: '.$palette['primary'],
            '--student-profile-accent-rgb: '.static::hexToRgbString($palette['primary']),
            '--student-profile-accent-hover: '.$palette['primary_hover'],
            '--student-profile-accent-soft: '.$palette['tint'],
            '--student-profile-accent-border: '.$palette['border'],
        ]);
    }

    protected static function mixHex(string $baseHex, string $targetHex, float $ratio): string
    {
        $ratio = max(0.0, min(1.0, $ratio));
        [$baseRed, $baseGreen, $baseBlue] = static::hexToRgb($baseHex);
        [$targetRed, $targetGreen, $targetBlue] = static::hexToRgb($targetHex);

        $red = (int) round(($baseRed * (1 - $ratio)) + ($targetRed * $ratio));
        $green = (int) round(($baseGreen * (1 - $ratio)) + ($targetGreen * $ratio));
        $blue = (int) round(($baseBlue * (1 - $ratio)) + ($targetBlue * $ratio));

        return sprintf('#%02X%02X%02X', $red, $green, $blue);
    }

    protected static function hexToRgbString(string $hex): string
    {
        [$red, $green, $blue] = static::hexToRgb($hex);

        return implode(', ', [$red, $green, $blue]);
    }

    /**
     * @return array{0: int, 1: int, 2: int}
     */
    protected static function hexToRgb(string $hex): array
    {
        $normalizedHex = ltrim($hex, '#');

        if (strlen($normalizedHex) === 3) {
            $normalizedHex = preg_replace('/(.)/', '$1$1', $normalizedHex) ?? $normalizedHex;
        }

        return [
            hexdec(substr($normalizedHex, 0, 2)),
            hexdec(substr($normalizedHex, 2, 2)),
            hexdec(substr($normalizedHex, 4, 2)),
        ];
    }
}
