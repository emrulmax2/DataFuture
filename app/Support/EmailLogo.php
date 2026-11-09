<?php

namespace App\Support;

use App\Models\Option;
use Illuminate\Support\Facades\Log;

/**
 * The company logo from Site Settings, sized for email.
 *
 * Emails embed it in their dark header rather than linking to it, so it shows
 * without the reader having to "load remote images". The uploaded file is a
 * print-sized PNG - around 400KB - and attaching that to every message is
 * wasteful enough that some clients refuse to show it, so a 420px copy is made
 * once and reused. If the logo is replaced in Site Settings the copy is older
 * than it and is rebuilt.
 */
class EmailLogo
{
    /** Absolute path to the email-sized logo, or null when Site Settings has none. */
    public static function path(): ?string
    {
        $file = cache('site_logo') ?: optional(Option::where('name', 'site_logo')->first())->value;

        if (! $file) {
            return null;
        }

        $source = storage_path('app/public/'.$file);

        if (! is_file($source)) {
            return null;
        }

        $small = storage_path('app/public/email/logo-email.png');

        if (is_file($small) && filemtime($small) >= filemtime($source)) {
            return $small;
        }

        return self::shrink($source, $small) ?: $source;
    }

    /**
     * The dark-lettered logo (Site Settings › dark logo, company_dark_logo.png), for emails
     * with a light header such as the induction invitation (user, 30 Oct 2026). Falls back
     * to path() when there is no dark logo.
     */
    public static function darkPath(): ?string
    {
        $file = optional(Option::where('name', 'site_dark_logo')->first())->value ?: 'company_dark_logo.png';
        $source = storage_path('app/public/'.$file);

        if (! is_file($source)) {
            return self::path();
        }

        $small = storage_path('app/public/email/logo-dark-email.png');

        if (is_file($small) && filemtime($small) >= filemtime($source)) {
            return $small;
        }

        return self::shrink($source, $small) ?: $source;
    }

    /** A width-constrained PNG copy, or null if GD could not make one. */
    private static function shrink(string $source, string $target, int $width = 420): ?string
    {
        if (! function_exists('imagecreatefrompng')) {
            return null;
        }

        try {
            $image = @imagecreatefrompng($source);

            if (! $image) {
                return null;
            }

            $height = (int) round(imagesy($image) * ($width / imagesx($image)));
            $small = imagecreatetruecolor($width, $height);

            // The crest sits on a dark header, so transparency has to survive.
            imagealphablending($small, false);
            imagesavealpha($small, true);
            imagecopyresampled($small, $image, 0, 0, 0, 0, $width, $height, imagesx($image), imagesy($image));

            if (! is_dir(dirname($target))) {
                mkdir(dirname($target), 0775, true);
            }

            imagepng($small, $target, 9);
            imagedestroy($image);
            imagedestroy($small);
        } catch (\Throwable $e) {
            Log::warning('[Email] Logo could not be resized.', ['error' => $e->getMessage()]);

            return null;
        }

        return is_file($target) ? $target : null;
    }
}
