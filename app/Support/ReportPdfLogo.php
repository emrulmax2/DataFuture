<?php

namespace App\Support;

use App\Models\Option;
use Illuminate\Support\Facades\Storage;

/**
 * The logo at the top left of the report PDFs (Admission Report, Continuation
 * Rate): the Dark Logo from Site Settings, or, while none is uploaded, the
 * placeholder Site Settings shows in its place. Shrunk and embedded as a data
 * URI, cached until the file changes.
 */
class ReportPdfLogo
{
    public static function dataUri(): ?string
    {
        $file = Option::where('category', 'SITE_SETTINGS')->where('name', 'site_dark_logo')->value('value');
        $path = (!empty($file) && Storage::disk('local')->exists('public/'.$file))
            ? Storage::disk('local')->path('public/'.$file)
            : resource_path('images/lcc-header-sample-logo.png');
        if (!is_file($path)) {
            return null;
        }

        return cache()->rememberForever('admission_report_pdf_logo_'.md5($path.'|'.filemtime($path)), function () use ($path) {
            return static::imageDataUri($path);
        });
    }

    /** A PNG taller than $height is resampled first (alpha kept), so the full-size upload does not ride along in every PDF. */
    protected static function imageDataUri(string $path, int $height = 150): string
    {
        $raw = (string) file_get_contents($path);
        $info = @getimagesize($path);
        $mime = (!empty($info['mime']) ? $info['mime'] : (str_ends_with(strtolower($path), '.svg') ? 'image/svg+xml' : 'image/png'));

        if ($mime === 'image/png' && !empty($info) && $info[1] > $height && extension_loaded('gd') && ($source = @imagecreatefrompng($path))) {
            $width = (int) max(1, round($info[0] * $height / $info[1]));
            $canvas = imagecreatetruecolor($width, $height);
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
            imagefill($canvas, 0, 0, imagecolorallocatealpha($canvas, 0, 0, 0, 127));
            imagecopyresampled($canvas, $source, 0, 0, 0, 0, $width, $height, $info[0], $info[1]);
            ob_start();
            imagepng($canvas, null, 9);
            $small = (string) ob_get_clean();
            if ($small !== '') {
                $raw = $small;
            }
        }

        return 'data:'.$mime.';base64,'.base64_encode($raw);
    }
}
