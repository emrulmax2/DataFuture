<?php

namespace App\Support;

use App\Models\Employee;
use Illuminate\Support\Facades\Storage;

/**
 * Renders the whole left-hand panel of the signature as one flat PNG: the pale
 * quadrant in the card's top corner, the navy and crimson arcs, and the
 * employee's photo inside them.
 *
 * None of that can be markup. The arcs are border-radius plus a CSS transform
 * and the quadrant is an absolutely positioned circle bleeding off two edges —
 * Outlook renders mail with Word, which supports none of it. Flattening the
 * panel into a single image is the only way it looks the same everywhere, and
 * it costs nothing at send time because the file is built once and reused
 * until the employee changes their photo.
 *
 * The panel is a fixed size so the quadrant stays flush with the corner: it is
 * the tallest cell in the row, so the row cannot grow taller than it and leave
 * the artwork floating.
 */
class SignatureAvatar
{
    /** Panel footprint in CSS pixels — 26px gutter, 168px portrait, 24px gutter. */
    const PANEL_W = 218;
    const PANEL_H = 320;

    /** Drawn at 3x and downsampled — GD has no antialiasing on filled arcs. */
    const SUPERSAMPLE = 3;

    const NAVY = [0x12, 0x29, 0x4A];
    const RED = [0xC8, 0x10, 0x2E];
    const QUADRANT = [0xF4, 0xF7, 0xFB];

    /** Centre of the portrait within the panel. */
    const PORTRAIT_X = 110;

    /**
     * Public URL of the composed panel, building it first if the cached file is
     * missing or older than the employee's photo. Returns '' if it could not be
     * produced at all.
     */
    public static function urlFor(Employee $employee){
        $relative = 'employees/'.$employee->id.'/signature-panel.png';
        $target = storage_path('app/public/'.$relative);
        $source = self::sourcePhotoPath($employee);

        $fresh = file_exists($target)
            && ($source === '' || filemtime($target) >= filemtime($source));

        if(!$fresh && !self::build($employee, $target, $source)):
            return '';
        endif;

        return Storage::disk('local')->url('public/'.$relative);
    }

    protected static function sourcePhotoPath(Employee $employee){
        if(empty($employee->photo)):
            return '';
        endif;
        $path = storage_path('app/public/employees/'.$employee->id.'/'.$employee->photo);

        return (file_exists($path) ? $path : '');
    }

    protected static function build(Employee $employee, $target, $source){
        if(!function_exists('imagecreatetruecolor')):
            return false;
        endif;

        $s = self::SUPERSAMPLE;
        $w = self::PANEL_W * $s;
        $h = self::PANEL_H * $s;
        $cx = self::PORTRAIT_X * $s;
        $cy = (int) ($h / 2);

        // Opaque white rather than transparent: the card behind is white, and a
        // flat image sidesteps every client's PNG-alpha quirks.
        $canvas = imagecreatetruecolor($w, $h);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, false);
        imagefilledrectangle($canvas, 0, 0, $w, $h, imagecolorallocate($canvas, 255, 255, 255));

        // The decorative quadrant: a 150px circle centred on the card's top-left
        // corner, so only its bottom-right quarter falls inside the panel.
        imagealphablending($canvas, true);
        imagefilledellipse($canvas, 0, 0, 300 * $s, 300 * $s, imagecolorallocate($canvas, self::QUADRANT[0], self::QUADRANT[1], self::QUADRANT[2]));

        // Navy band, 10px thick on an 84px radius, with the gap the CSS
        // border-right leaves once the ring is rotated -42deg.
        self::stamp($canvas, self::ring($w, $h, $cx, $cy, 84 * $s, 10 * $s, self::NAVY, 3, 273));
        // Crimson hairline, 2px thick and inset 7px, covering only the right and
        // bottom borders, rotated +20deg.
        self::stamp($canvas, self::ring($w, $h, $cx, $cy, 77 * $s, 2 * $s, self::RED, 335, 515));
        // White collar between the rings and the photo.
        self::stamp($canvas, self::ring($w, $h, $cx, $cy, 63 * $s, 5 * $s, [255, 255, 255], 0, 360));

        $face = ($source !== ''
            ? self::photoDisc($w, $h, $cx, $cy, 58 * $s, $source)
            : self::initialsDisc($w, $h, $cx, $cy, 58 * $s, $employee));
        self::stamp($canvas, $face);

        $final = imagecreatetruecolor(self::PANEL_W, self::PANEL_H);
        imagealphablending($final, false);
        imagesavealpha($final, false);
        imagecopyresampled($final, $canvas, 0, 0, 0, 0, self::PANEL_W, self::PANEL_H, $w, $h);
        imagedestroy($canvas);

        if(!is_dir(dirname($target))):
            @mkdir(dirname($target), 0775, true);
        endif;
        $ok = imagepng($final, $target, 9);
        imagedestroy($final);

        return (bool) $ok;
    }

    protected static function transparent($w, $h){
        $img = imagecreatetruecolor($w, $h);
        imagealphablending($img, false);
        imagesavealpha($img, true);
        imagefill($img, 0, 0, imagecolorallocatealpha($img, 0, 0, 0, 127));

        return $img;
    }

    /**
     * An arc band: a filled pie with its middle punched back out to
     * transparent, drawn on its own layer so bands can overlap.
     */
    protected static function ring($w, $h, $cx, $cy, $outerR, $thickness, $rgb, $start, $end){
        $layer = self::transparent($w, $h);
        $colour = imagecolorallocate($layer, $rgb[0], $rgb[1], $rgb[2]);
        imagefilledarc($layer, $cx, $cy, $outerR * 2, $outerR * 2, $start, $end, $colour, IMG_ARC_PIE);

        $innerR = $outerR - $thickness;
        imagefilledellipse($layer, $cx, $cy, $innerR * 2, $innerR * 2, imagecolorallocatealpha($layer, 0, 0, 0, 127));

        return $layer;
    }

    protected static function photoDisc($w, $h, $cx, $cy, $r, $path){
        $layer = self::transparent($w, $h);
        $raw = @file_get_contents($path);
        $src = ($raw !== false ? @imagecreatefromstring($raw) : false);
        if(!$src):
            return $layer;
        endif;

        // Centre-crop to a square so faces are not stretched, then scale to
        // the disc's bounding box.
        $sw = imagesx($src);
        $sh = imagesy($src);
        $side = min($sw, $sh);
        $square = imagecreatetruecolor($r * 2, $r * 2);
        imagecopyresampled($square, $src, 0, 0, (int) (($sw - $side) / 2), (int) (($sh - $side) / 2), $r * 2, $r * 2, $side, $side);
        imagedestroy($src);

        $r2 = $r * $r;
        for($y = 0; $y < $r * 2; $y++):
            for($x = 0; $x < $r * 2; $x++):
                $dx = $x - $r + 0.5;
                $dy = $y - $r + 0.5;
                if(($dx * $dx) + ($dy * $dy) <= $r2):
                    imagesetpixel($layer, $cx - $r + $x, $cy - $r + $y, imagecolorat($square, $x, $y));
                endif;
            endfor;
        endfor;
        imagedestroy($square);

        return $layer;
    }

    /**
     * No photo on file: a navy disc carrying the employee's initials, so the
     * ring artwork still reads as intended.
     */
    protected static function initialsDisc($w, $h, $cx, $cy, $r, Employee $employee){
        $layer = self::transparent($w, $h);
        imagealphablending($layer, true);
        imagefilledellipse($layer, $cx, $cy, $r * 2, $r * 2, imagecolorallocate($layer, self::NAVY[0], self::NAVY[1], self::NAVY[2]));

        $initials = mb_strtoupper(mb_substr((string) $employee->first_name, 0, 1).mb_substr((string) $employee->last_name, 0, 1));
        if($initials === ''):
            return $layer;
        endif;

        $font = resource_path('fonts/plus-jakarta-sans/PlusJakartaSans-Bold.ttf');
        if(!file_exists($font) || !function_exists('imagettftext')):
            return $layer;
        endif;

        $fontSize = (int) ($r * 0.72);
        $box = imagettfbbox($fontSize, 0, $font, $initials);
        $textW = $box[2] - $box[0];
        $textH = $box[1] - $box[7];
        imagettftext(
            $layer,
            $fontSize,
            0,
            (int) ($cx - ($textW / 2) - $box[0]),
            (int) ($cy + ($textH / 2) - $box[1]),
            imagecolorallocate($layer, 255, 255, 255),
            $font,
            $initials
        );

        return $layer;
    }

    protected static function stamp($canvas, $layer){
        imagealphablending($canvas, true);
        imagecopy($canvas, $layer, 0, 0, 0, 0, imagesx($layer), imagesy($layer));
        imagedestroy($layer);
    }
}
