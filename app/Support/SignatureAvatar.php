<?php

namespace App\Support;

use App\Models\Employee;
use Illuminate\Support\Facades\Storage;

/**
 * Renders the whole left-hand panel of the signature as one flat PNG: the pale
 * quadrant in the card's top corner, the navy and crimson arcs, and the
 * employee's photo inside them. The mobile signature gets the same mark as a
 * compact round badge.
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
 *
 * Two things govern how sharp the portrait ends up, and both are deliberate.
 * The artwork is always shipped at a higher pixel density than it is displayed
 * at, because a signature is read on Retina laptops and phones where a 1x image
 * is visibly soft. And the photo is composited *after* the canvas has been
 * scaled down, so it is resampled exactly once, straight from the upload to the
 * size that ships — routing it through the supersampled canvas as well cost
 * about a sixth of its detail.
 */
class SignatureAvatar
{
    /** Panel footprint in CSS pixels — 26px gutter, 168px portrait, 24px gutter. */
    const PANEL_W = 218;
    const PANEL_H = 320;

    /**
     * The mobile signature has no room for the tall panel, so it carries the
     * same mark as a square badge instead: the rings at a smaller radius, on
     * the pale tint its header block sits on.
     */
    const BADGE = 88;

    /** Centre of the portrait within the panel. */
    const PORTRAIT_X = 110;

    /** Portrait radius in CSS pixels, per artefact. */
    const PANEL_R = 58;
    const BADGE_R = 28;

    /**
     * The collar ring's inner edge sits exactly on the portrait radius. Drawing
     * the photo one shipped pixel proud of it tucks the two together, so the
     * seam cannot show the background between them.
     */
    const SEAM = 1;

    /**
     * Ceiling on the source photo: how many pixels it may carry, and how much
     * memory one decode of it may claim. Both are generous — 40MP covers every
     * camera and phone staff actually upload from, and 384MB is enough to open
     * one with the request's own footprint still sitting underneath it.
     */
    const MAX_SOURCE_PIXELS = 40000000;
    const MEMORY_CEILING = 402653184;

    /** Left free for whatever the request still has to do after the decode. */
    const MEMORY_HEADROOM = 25165824;

    /** Arcs are drawn at 3x and scaled down — GD has no antialiasing on them. */
    const SUPERSAMPLE = 3;

    const NAVY = [0x12, 0x29, 0x4A];
    const RED = [0xC8, 0x10, 0x2E];
    const QUADRANT = [0xF4, 0xF7, 0xFB];

    /**
     * Public URL of the composed panel, building it first if the cached file is
     * missing or older than the employee's photo. Returns '' if it could not be
     * produced at all.
     *
     * The markup always declares PANEL_W x PANEL_H whatever the scale, so a
     * higher density buys sharpness rather than size.
     */
    public static function urlFor(Employee $employee, $scale = 2){
        return self::cachedUrl($employee, 'panel', $scale);
    }

    /**
     * Public URL of the round badge, for the mobile signature. Phones run at
     * two to three device pixels per CSS pixel, so that one is drawn at 3x.
     */
    public static function badgeUrlFor(Employee $employee, $scale = 3){
        return self::cachedUrl($employee, 'badge', $scale);
    }

    /**
     * Both artefacts are cached per employee and rebuilt only when the file is
     * missing or the photo behind it has changed, so the cost falls on the
     * first render after a new picture and on nothing else.
     */
    protected static function cachedUrl(Employee $employee, $kind, $scale){
        $scale = (int) max(1, min(3, $scale));
        // "-2x" rather than the usual "@2x": the URL is handed to mail clients
        // and image proxies, and there is nothing to gain from an "@" in a path.
        //
        // "v2" is a cache epoch rather than decoration. Every signature built
        // before large photos could be decoded is cached as an initials panel
        // stamped later than the photo it failed to open, so the freshness
        // check below would go on serving it for good; moving the name retires
        // that whole generation at once and costs one rebuild per employee.
        $file = 'signature-'.$kind.'-v2'.($scale > 1 ? '-'.$scale.'x' : '').'.png';
        $relative = 'employees/'.$employee->id.'/'.$file;
        $target = storage_path('app/public/'.$relative);
        $source = self::sourcePhotoPath($employee);

        $fresh = file_exists($target)
            && ($source === '' || filemtime($target) >= filemtime($source));

        if(!$fresh):
            $built = ($kind === 'badge'
                ? self::buildBadge($employee, $target, $source, $scale)
                : self::build($employee, $target, $source, $scale));
            if(!$built):
                return '';
            endif;
        endif;

        return Storage::disk('local')->url('public/'.$relative);
    }

    /**
     * The best copy of the employee's photo on disk.
     *
     * The photo column names the 256px thumbnail HR generates for the staff
     * list, which is smaller than the portrait is drawn at — it was being
     * upscaled into the ring and came out soft. The full-size upload sits
     * beside it under the same name without the "_thumb", so that is preferred
     * and the thumbnail stays as the fallback.
     */
    protected static function sourcePhotoPath(Employee $employee){
        if(empty($employee->photo)):
            return '';
        endif;

        $dir = storage_path('app/public/employees/'.$employee->id.'/');

        $names = [];
        if(str_contains($employee->photo, '_thumb.')):
            $names[] = str_replace('_thumb.', '.', $employee->photo);
        endif;
        $names[] = $employee->photo;

        foreach($names as $name):
            if(file_exists($dir.$name) && self::withinMemory($dir.$name)):
                return $dir.$name;
            endif;
        endforeach;

        return '';
    }

    /**
     * Whether this photo can be decoded at all.
     *
     * GD holds an image uncompressed at four bytes a pixel and has no
     * shrink-on-load, so the entire frame has to fit in memory before anything
     * can be cropped out of it: a 12MP phone photo is 6MB on disk and about
     * 50MB decoded. What decides it is therefore not the memory limit but how
     * much of the limit is still unspent, and My HR has already claimed most
     * of a default 128M by the time the signature renders. Measuring the frame
     * against the whole limit instead was why an ordinary 4MB phone photo
     * either landed on the initials fallback or, just under the old ceiling,
     * exhausted memory outright.
     *
     * Where the headroom is short the limit is lifted for the decode and put
     * back afterwards, as the heavier reports already do. Only a photo past
     * MAX_SOURCE_PIXELS, or one that would not fit even then, falls through to
     * the thumbnail and finally to initials.
     */
    protected static function withinMemory($path){
        $size = @getimagesize($path);
        if(!$size):
            return false;
        endif;

        if(($size[0] * $size[1]) > self::MAX_SOURCE_PIXELS):
            return false;
        endif;

        return (self::decodeFootprint($path, $size) <= self::MEMORY_CEILING);
    }

    /**
     * Peak bytes the request would hold while the photo is decoded: what it has
     * already claimed, the encoded file, and GD's uncompressed copy of the
     * frame with a margin for its per-row index.
     */
    protected static function decodeFootprint($path, $size){
        return (int) (memory_get_usage(true)
            + (int) @filesize($path)
            + ($size[0] * $size[1] * 4 * 1.1)
            + self::MEMORY_HEADROOM);
    }

    /**
     * Lifts memory_limit high enough for one decode of $footprint bytes.
     * Returns the value to hand back to restoreMemory(), NULL when the limit
     * was already sufficient, or false if the room could not be found — a host
     * with ini_set locked down, say.
     */
    protected static function reserveMemory($footprint){
        $limit = self::memoryLimit();
        if($limit < 0 || $footprint <= $limit):
            return null;
        endif;

        // A host that locks these away cannot be given more room, so the photo
        // falls through to the thumbnail instead. Tested with function_exists
        // rather than "@": disable_functions makes the name undefined outright
        // in PHP 8, and the silence operator does not suppress an Error.
        if($footprint > self::MEMORY_CEILING || !function_exists('ini_set') || !function_exists('ini_get')):
            return false;
        endif;

        $previous = ini_get('memory_limit');
        if(@ini_set('memory_limit', (string) $footprint) === false):
            return false;
        endif;

        return $previous;
    }

    /**
     * Hands the limit back once the decoded frame has been released.
     *
     * Best effort by design: PHP refuses to set memory_limit below what the
     * request is currently holding, and the allocator does not always return
     * the frame's chunks to the OS the moment GD frees them. Where that
     * happens the raised limit simply stands for the rest of this one request
     * — it is not inherited by the next — which is the same bargain the heavy
     * reports make when they open with ini_set('memory_limit', '512M').
     */
    protected static function restoreMemory($previous){
        if(is_string($previous) && $previous !== '' && function_exists('ini_set')):
            @ini_set('memory_limit', $previous);
        endif;
    }

    /** memory_limit in bytes, or -1 where the request is not capped. */
    protected static function memoryLimit(){
        // With ini_get disabled there is no way to read the limit, so assume
        // the PHP default: guessing high is what would put a fatal error on
        // the page instead of a signature that merely falls back to initials.
        if(!function_exists('ini_get')):
            return 134217728;
        endif;

        $limit = trim((string) ini_get('memory_limit'));
        if($limit === '' || (int) $limit < 0):
            return -1;
        endif;

        $value = (int) $limit;
        $unit = strtolower(substr($limit, -1));
        if($unit === 'g'):
            return $value * 1024 * 1024 * 1024;
        elseif($unit === 'm'):
            return $value * 1024 * 1024;
        elseif($unit === 'k'):
            return $value * 1024;
        endif;

        return $value;
    }

    protected static function build(Employee $employee, $target, $source, $scale = 2){
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

        $final = self::downsample($canvas, self::PANEL_W * $scale, self::PANEL_H * $scale);

        // The portrait goes on last, at the size that ships, so the upload is
        // resampled once rather than once into the 3x canvas and again out of it.
        self::stampPortrait(
            $final,
            self::PORTRAIT_X * $scale,
            (self::PANEL_H / 2) * $scale,
            (self::PANEL_R * $scale) + self::SEAM,
            $source,
            $employee
        );

        return self::save($final, $target);
    }

    /**
     * The same three bands as the panel at a smaller radius, so the desktop
     * card and the mobile one read as the same mark. Proportions are kept
     * rather than the absolute sizes: the portrait fills the same share of the
     * outer ring either way.
     */
    protected static function buildBadge(Employee $employee, $target, $source, $scale = 3){
        if(!function_exists('imagecreatetruecolor')):
            return false;
        endif;

        $s = self::SUPERSAMPLE;
        $w = self::BADGE * $s;
        $cx = (int) ($w / 2);

        // Flattened onto the tint the mobile header sits on. A transparent PNG
        // would leave the arcs fringed against whatever the client paints in.
        $canvas = imagecreatetruecolor($w, $w);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, false);
        imagefilledrectangle($canvas, 0, 0, $w, $w, imagecolorallocate($canvas, self::QUADRANT[0], self::QUADRANT[1], self::QUADRANT[2]));
        imagealphablending($canvas, true);

        self::stamp($canvas, self::ring($w, $w, $cx, $cx, 40 * $s, 5 * $s, self::NAVY, 3, 273));
        self::stamp($canvas, self::ring($w, $w, $cx, $cx, 36 * $s, 1.5 * $s, self::RED, 335, 515));
        self::stamp($canvas, self::ring($w, $w, $cx, $cx, 30 * $s, 2.5 * $s, [255, 255, 255], 0, 360));

        $out = self::BADGE * $scale;
        $final = self::downsample($canvas, $out, $out);

        self::stampPortrait(
            $final,
            $out / 2,
            $out / 2,
            (self::BADGE_R * $scale) + self::SEAM,
            $source,
            $employee
        );

        return self::save($final, $target);
    }

    /** Scales the supersampled artwork down to the size that ships. */
    protected static function downsample($canvas, $outW, $outH){
        $final = imagecreatetruecolor($outW, $outH);
        imagealphablending($final, false);
        imagesavealpha($final, false);
        imagecopyresampled($final, $canvas, 0, 0, 0, 0, $outW, $outH, imagesx($canvas), imagesy($canvas));
        imagedestroy($canvas);

        return $final;
    }

    protected static function save($final, $target){
        if(!is_dir(dirname($target))):
            @mkdir(dirname($target), 0775, true);
        endif;
        $ok = imagepng($final, $target, 9);
        imagedestroy($final);

        return (bool) $ok;
    }

    /**
     * Composites the round portrait onto the finished canvas, falling back to
     * the employee's initials when there is no usable photo.
     */
    protected static function stampPortrait($canvas, $cx, $cy, $r, $source, Employee $employee){
        $size = (int) round($r * 2);
        $disc = ($source !== '' ? self::photoSquare($size, $source) : false);
        if(!$disc):
            $disc = self::initialsSquare($size, $employee);
        endif;

        self::maskToCircle($canvas, $disc, $cx, $cy, $r);
        imagedestroy($disc);
    }

    /**
     * A centre-cropped square of the employee's photo at exactly the size it
     * will occupy — one resample, straight from the upload.
     */
    protected static function photoSquare($size, $path){
        $source = @getimagesize($path);
        if(!$source):
            return false;
        endif;

        // The decode is the only large allocation in the build — everything
        // after it works on a square a couple of hundred pixels across — so the
        // limit is lifted around that alone.
        $previous = self::reserveMemory(self::decodeFootprint($path, $source));
        if($previous === false):
            return false;
        endif;

        try{
            $raw = @file_get_contents($path);
            $src = ($raw !== false ? @imagecreatefromstring($raw) : false);
            // The encoded copy is dead weight once GD has the frame, and on a
            // phone photo it is several megabytes of it.
            unset($raw);
            if(!$src):
                return false;
            endif;

            // Centre-crop to a square first so faces are not stretched.
            $sw = imagesx($src);
            $sh = imagesy($src);
            $side = min($sw, $sh);

            $square = imagecreatetruecolor($size, $size);
            imagealphablending($square, false);
            imagesavealpha($square, false);
            imagecopyresampled($square, $src, 0, 0, (int) (($sw - $side) / 2), (int) (($sh - $side) / 2), $size, $size, $side, $side);
            imagedestroy($src);

            return $square;
        } finally {
            // Attempted only once the frame is released — see restoreMemory()
            // for why it does not always take.
            self::restoreMemory($previous);
        }
    }

    /**
     * No photo on file: a navy field carrying the employee's initials, so the
     * ring artwork still reads as intended. Squared off because the caller
     * clips it to a circle.
     */
    protected static function initialsSquare($size, Employee $employee){
        $square = imagecreatetruecolor($size, $size);
        imagealphablending($square, false);
        imagesavealpha($square, false);
        imagefilledrectangle($square, 0, 0, $size, $size, imagecolorallocate($square, self::NAVY[0], self::NAVY[1], self::NAVY[2]));

        $initials = mb_strtoupper(mb_substr((string) $employee->first_name, 0, 1).mb_substr((string) $employee->last_name, 0, 1));
        $font = resource_path('fonts/plus-jakarta-sans/PlusJakartaSans-Bold.ttf');
        if($initials === '' || !file_exists($font) || !function_exists('imagettftext')):
            return $square;
        endif;

        imagealphablending($square, true);
        $fontSize = (int) ($size * 0.36);
        $box = imagettfbbox($fontSize, 0, $font, $initials);
        $textW = $box[2] - $box[0];
        $textH = $box[1] - $box[7];
        imagettftext(
            $square,
            $fontSize,
            0,
            (int) (($size / 2) - ($textW / 2) - $box[0]),
            (int) (($size / 2) + ($textH / 2) - $box[1]),
            imagecolorallocate($square, 255, 255, 255),
            $font,
            $initials
        );

        return $square;
    }

    /**
     * Blends a square image onto the canvas through a circular mask.
     *
     * The rim is the only part that costs anything: inside it the pixel is
     * copied straight across, outside it is skipped, and only the one-pixel
     * band between gets sub-sampled for coverage. Without that the portrait
     * would meet the white collar in a staircase, because this runs after the
     * canvas has been scaled down and there is no supersampling left to hide it.
     */
    protected static function maskToCircle($canvas, $disc, $cx, $cy, $r){
        $size = (int) round($r * 2);
        $left = (int) round($cx - $r);
        $top = (int) round($cy - $r);
        $inner = ($r - 0.75) * ($r - 0.75);
        $outer = ($r + 0.75) * ($r + 0.75);
        $exact = $r * $r;

        imagealphablending($canvas, false);

        for($y = 0; $y < $size; $y++):
            for($x = 0; $x < $size; $x++):
                $dx = $x - $r + 0.5;
                $dy = $y - $r + 0.5;
                $d = ($dx * $dx) + ($dy * $dy);
                if($d >= $outer):
                    continue;
                endif;

                $colour = imagecolorat($disc, $x, $y) & 0xFFFFFF;

                if($d <= $inner):
                    imagesetpixel($canvas, $left + $x, $top + $y, $colour);
                    continue;
                endif;

                $hits = 0;
                for($sy = 0; $sy < 4; $sy++):
                    for($sx = 0; $sx < 4; $sx++):
                        $ux = $x - $r + (($sx + 0.5) / 4);
                        $uy = $y - $r + (($sy + 0.5) / 4);
                        if((($ux * $ux) + ($uy * $uy)) <= $exact):
                            $hits++;
                        endif;
                    endfor;
                endfor;
                if($hits === 0):
                    continue;
                endif;

                $a = $hits / 16;
                $under = imagecolorat($canvas, $left + $x, $top + $y) & 0xFFFFFF;
                $red = (int) round((($colour >> 16) & 255) * $a + (($under >> 16) & 255) * (1 - $a));
                $green = (int) round((($colour >> 8) & 255) * $a + (($under >> 8) & 255) * (1 - $a));
                $blue = (int) round(($colour & 255) * $a + ($under & 255) * (1 - $a));
                imagesetpixel($canvas, $left + $x, $top + $y, ($red << 16) | ($green << 8) | $blue);
            endfor;
        endfor;
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

    protected static function stamp($canvas, $layer){
        imagealphablending($canvas, true);
        imagecopy($canvas, $layer, 0, 0, 0, 0, imagesx($layer), imagesy($layer));
        imagedestroy($layer);
    }
}
