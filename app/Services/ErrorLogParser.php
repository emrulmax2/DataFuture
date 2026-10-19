<?php

namespace App\Services;

/**
 * Reads entries written by Laravel's Monolog line formatter:
 *
 *   [2026-10-13 16:22:14] production.ERROR: message {"userId":313,"exception":"[object] (Class(code: 0): message at /path/file.php:49)
 *   [stacktrace]
 *   #0 /path/file.php(60): Class->method()
 *   ...
 *   [previous exception] [object] (Class(code: 0): message at /path/file.php:49)
 *   [stacktrace]
 *   ...
 *   "}
 *
 * The context is JSON, but the formatter turns the escaped line breaks back
 * into real ones, so the stack trace cannot be json_decode()d as written. The
 * exception chain is therefore read by pattern, and strings taken from inside
 * the context are unescaped (\\ and \") before they are shown.
 */
class ErrorLogParser
{
    const HEADER = '/^\[(\d{4}-\d{2}-\d{2}[T ]\d{2}:\d{2}:\d{2})(?:\.\d+)?(?:Z|[+-]\d{2}:?\d{2})?\] ([^\s.\[\]]+)\.([A-Z]+): ?(.*)$/s';

    const EXCEPTION_MARKER = '"exception":"[object] (';

    const FRAME = '/^#(?<n>\d+) (?:\[internal function\]: (?<icall>.*)|(?<main>\{main\})|(?<file>.+?)\((?<line>\d+)\): (?<call>.*))$/';

    const MESSAGE_MAX = 300;

    const FRAMES_MAX = 500;

    const LEVELS = ['EMERGENCY' => 8, 'ALERT' => 7, 'CRITICAL' => 6, 'ERROR' => 5, 'WARNING' => 4, 'NOTICE' => 3, 'INFO' => 2, 'DEBUG' => 1];

    private static $viewSources = [];

    /**
     * The datetime, environment, level and the text after "LEVEL: " of an
     * entry's first line, or null when the line does not start an entry.
     */
    public static function header(string $line): ?array
    {
        if(!isset($line[0]) || $line[0] !== '[' || !preg_match(self::HEADER, rtrim($line, "\r\n"), $m)):
            return null;
        endif;

        return ['datetime' => str_replace('T', ' ', $m[1]), 'env' => $m[2], 'level' => $m[3], 'rest' => $m[4]];
    }

    /**
     * The compact index row for one entry (offset and length are added by the
     * caller): [timestamp, env, level, message, exception class, where, user id, fingerprint].
     *
     * $exception is the entry's text from "[object] (" through its first stack
     * frames; $exceptionOnFirstLine says whether that marker sat on the header
     * line (otherwise a large context, e.g. view data, came before it).
     */
    public static function summary(string $firstLine, string $exception, bool $exceptionOnFirstLine, $userId): ?array
    {
        $header = self::header($firstLine);
        if(!$header):
            return null;
        endif;

        $message = self::messagePart(rtrim($header['rest']), $exception !== '', $exceptionOnFirstLine);
        $class = '';
        $where = '';

        if($exception !== ''):
            $parts = explode("\n[stacktrace]", $exception, 2);
            $parsed = self::exceptionHeader($parts[0]);
            if($parsed):
                $class = self::unescape($parsed['class']);
                $where = self::locate($message, $parsed, isset($parts[1]) ? $parts[1] : '');
                if(trim($message) === ''):
                    $message = self::unescape($parsed['message']);
                endif;
            endif;
        endif;

        $message = self::shortenPaths(trim($message));
        $timestamp = strtotime($header['datetime']);

        return [
            ($timestamp ? $timestamp : 0),
            $header['env'],
            $header['level'],
            self::cut($message, self::MESSAGE_MAX),
            $class,
            $where,
            ($userId !== null ? (int) $userId : null),
            self::fingerprint($header['level'], $class, $where, $message),
        ];
    }

    /**
     * Everything the detail view shows for one entry. $text is the entry as it
     * sits in the file (possibly cut short, see $truncated).
     */
    public static function detail(string $text, bool $truncated): ?array
    {
        $newline = strpos($text, "\n");
        $firstLine = rtrim($newline === false ? $text : substr($text, 0, $newline), "\r");
        $header = self::header($firstLine);
        if(!$header):
            return null;
        endif;

        $restOffset = strlen($firstLine) - strlen($header['rest']);
        $marker = strpos($text, self::EXCEPTION_MARKER);
        $message = $header['rest'];
        $context = [];
        $exceptions = [];

        if($marker !== false):
            $markerOnFirstLine = $marker < strlen($firstLine);
            // +1 keeps the marker's opening quote, for when "exception" is the
            // first key: ' {"exception"...'.
            $contextStart = ($markerOnFirstLine
                ? strrpos(substr($header['rest'], 0, $marker - $restOffset + 1), ' {"')
                : strpos($header['rest'], ' {"'));

            if($contextStart !== false):
                $message = substr($header['rest'], 0, $contextStart);
                $contextFrom = $restOffset + $contextStart + 1;
                $context = self::contextRows(substr($text, $contextFrom, $marker - $contextFrom), true);
            endif;

            $chain = substr($text, $marker + strlen('"exception":"'));
            if(!$truncated):
                $chain = self::stripContextClose($chain);
            endif;

            foreach(preg_split('/\r?\n\[previous exception\] /', $chain) as $block):
                $exceptions[] = self::parseException($block);
            endforeach;
        else:
            $body = rtrim($header['rest'].($newline === false ? '' : substr($text, $newline)));
            $split = self::splitTrailingJson($body);
            if($split !== null):
                $message = $split[0];
                $context = self::contextRows($split[1], false);
            else:
                $message = $body;
            endif;
        endif;

        $message = self::shortenPaths(trim($message));
        if($message === '' && !empty($exceptions)):
            $message = $exceptions[0]['message'];
        endif;

        return [
            'datetime' => $header['datetime'],
            'env' => $header['env'],
            'level' => $header['level'],
            'message' => $message,
            'context' => $context,
            'exceptions' => $exceptions,
        ];
    }

    /**
     * The message part of the header line, without the context JSON after it.
     */
    private static function messagePart(string $rest, bool $hasException, bool $exceptionOnFirstLine): string
    {
        if($hasException):
            if($exceptionOnFirstLine):
                $marker = strpos($rest, self::EXCEPTION_MARKER);
                $start = ($marker !== false ? strrpos(substr($rest, 0, $marker + 1), ' {"') : false);
            else:
                $start = strpos($rest, ' {"');
            endif;

            return ($start !== false ? substr($rest, 0, $start) : $rest);
        endif;

        $split = self::splitTrailingJson($rest);

        return ($split !== null ? $split[0] : $rest);
    }

    /**
     * Splits "message {json}" (optionally followed by an empty "[]" extra) into
     * [message, json] when the tail really is JSON; null otherwise.
     */
    private static function splitTrailingJson(string $text): ?array
    {
        $text = preg_replace('/ \[\]$/', '', $text);
        $from = 0;

        for($tries = 0; $tries < 8; $tries++):
            $brace = strpos($text, ' {', $from);
            $bracket = strpos($text, ' [', $from);
            if($brace === false && $bracket === false):
                return null;
            endif;

            $pos = ($brace === false ? $bracket : ($bracket === false ? $brace : min($brace, $bracket)));
            $tail = substr($text, $pos + 1);
            if(is_array(self::decodeJson($tail))):
                return [substr($text, 0, $pos), $tail];
            endif;

            $from = $pos + 1;
        endfor;

        return null;
    }

    /**
     * Decodes context JSON whose escaped line breaks the formatter turned back
     * into real ones.
     */
    private static function decodeJson(string $json)
    {
        return json_decode(str_replace(["\r\n", "\r", "\n"], '\n', $json), true);
    }

    /**
     * Context as [{key, value}] rows. $beforeException is the part of the
     * context that precedes "exception" (it ends with a dangling comma).
     */
    private static function contextRows(string $json, bool $beforeException): array
    {
        $json = trim($json);
        if($beforeException):
            $json = rtrim($json, ', ').'}';
        endif;

        $decoded = self::decodeJson($json);
        if(!is_array($decoded)):
            return (preg_match('/"userId":(\d+)/', $json, $m) ? [['key' => 'userId', 'value' => $m[1]]] : []);
        endif;

        $rows = [];
        foreach($decoded as $key => $value):
            if($key === 'view' && is_array($value) && isset($value['view'])):
                $rows[] = ['key' => 'view', 'value' => self::relativePath((string) $value['view'])];
                if(isset($value['data'])):
                    $rows[] = ['key' => 'view data', 'value' => 'Hidden ('.self::bytes(strlen(json_encode($value['data'], JSON_INVALID_UTF8_SUBSTITUTE))).')'];
                endif;
                continue;
            endif;

            if(is_array($value)):
                $value = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
            elseif(is_bool($value)):
                $value = ($value ? 'true' : 'false');
            elseif($value === null):
                $value = 'null';
            endif;

            $rows[] = ['key' => (string) $key, 'value' => self::cut((string) $value, 8192)];
        endforeach;

        return $rows;
    }

    /**
     * One exception block: "[object] (Class(code: X): message at file:line)"
     * followed by "[stacktrace]" and its frames.
     */
    private static function parseException(string $block): array
    {
        $parts = preg_split('/\r?\n\[stacktrace\]\r?\n/', $block, 2);
        $parsed = self::exceptionHeader($parts[0]);
        if(!$parsed):
            $parsed = ['class' => '', 'code' => '', 'message' => self::stripContextClose($parts[0]), 'file' => '', 'line' => 0];
        endif;

        $file = ($parsed['file'] !== '' ? self::relativePath(self::unescape($parsed['file'])) : '');
        $frames = [];

        if(isset($parts[1])):
            foreach(preg_split('/\r?\n/', $parts[1]) as $line):
                if(count($frames) >= self::FRAMES_MAX || !preg_match(self::FRAME, $line, $m)):
                    continue;
                endif;

                if(isset($m['main']) && $m['main'] !== ''):
                    $frames[] = ['n' => (int) $m['n'], 'file' => '', 'line' => 0, 'call' => '{main}', 'kind' => 'main', 'view' => null];
                elseif(isset($m['file']) && $m['file'] !== ''):
                    $rel = self::relativePath(self::unescape($m['file']));
                    $kind = self::kind($rel);
                    $frames[] = [
                        'n' => (int) $m['n'],
                        'file' => $rel,
                        'line' => (int) $m['line'],
                        'call' => self::unescape($m['call']),
                        'kind' => $kind,
                        'view' => ($kind === 'view' ? self::compiledViewSource($rel) : null),
                    ];
                else:
                    $frames[] = ['n' => (int) $m['n'], 'file' => '', 'line' => 0, 'call' => '[internal function] '.self::unescape($m['icall']), 'kind' => 'internal', 'view' => null];
                endif;
            endforeach;
        endif;

        $kind = ($file !== '' ? self::kind($file) : '');

        return [
            'class' => self::unescape($parsed['class']),
            'code' => $parsed['code'],
            'message' => self::shortenPaths(self::unescape($parsed['message'])),
            'file' => $file,
            'line' => (int) $parsed['line'],
            'kind' => $kind,
            'view' => ($kind === 'view' ? self::compiledViewSource($file) : null),
            'frames' => $frames,
        ];
    }

    /**
     * "[object] (Class(code: X): message at /path/file.php:12)" into its parts.
     * The message may itself contain " at ", so the last one before the
     * closing "file:line)" wins.
     */
    private static function exceptionHeader(string $text): ?array
    {
        $text = self::stripContextClose($text);
        if(preg_match('/^\[object\] \((?<class>[^\s(]+)\(code: (?<code>[^)]*)\): (?<message>.*) at (?<file>.+?):(?<line>\d+)\)$/s', $text, $m)):
            return ['class' => $m['class'], 'code' => $m['code'], 'message' => $m['message'], 'file' => $m['file'], 'line' => (int) $m['line']];
        endif;

        if(preg_match('/^\[object\] \((?<class>[^\s(]+)\(code: (?<code>[^)]*)\): (?<message>.*)$/s', $text, $m)):
            return ['class' => $m['class'], 'code' => $m['code'], 'message' => $m['message'], 'file' => '', 'line' => 0];
        endif;

        return null;
    }

    /**
     * Where in our own code the entry points: the Blade view for view errors,
     * else the throwing file when it is ours, else the first frame that is ours,
     * else the (framework) throwing file.
     */
    private static function locate(string $message, array $exception, string $frames): string
    {
        if(preg_match('/\(View: (.+?)\)/', $message, $m)):
            return self::relativePath($m[1]);
        endif;

        $file = ($exception['file'] !== '' ? self::relativePath(self::unescape($exception['file'])) : '');
        $kind = ($file !== '' ? self::kind($file) : '');
        if($kind === 'app'):
            return $file.':'.$exception['line'];
        endif;
        if($kind === 'view' && ($source = self::compiledViewSource($file))):
            return $source;
        endif;

        foreach(preg_split('/\r?\n/', $frames) as $line):
            if(preg_match(self::FRAME, $line, $m) && isset($m['file']) && $m['file'] !== ''):
                $rel = self::relativePath(self::unescape($m['file']));
                if(self::kind($rel) === 'app'):
                    return $rel.':'.$m['line'];
                endif;
            endif;
        endforeach;

        return ($file !== '' ? $file.':'.$exception['line'] : '');
    }

    /**
     * Groups repeats of the same problem: numbers, quoted values and long
     * hashes are blanked so "user 5" and "user 9" land together.
     */
    public static function fingerprint(string $level, string $class, string $where, string $message): string
    {
        $text = mb_strcut($message, 0, 240, 'UTF-8');
        $text = preg_replace("/'[^']*'/", "'?'", $text);
        $text = preg_replace('/\b[0-9a-f]{12,}\b/i', 'X', $text);
        $text = preg_replace('/\d+/', 'N', $text);

        return substr(md5($level.'|'.$class.'|'.$where.'|'.$text), 0, 16);
    }

    /**
     * A path relative to the project (or to the nearest known root when the
     * entry came from another machine, e.g. production paths read locally).
     */
    public static function relativePath(string $path): string
    {
        $path = str_replace('\\', '/', $path);
        $base = rtrim(str_replace('\\', '/', base_path()), '/').'/';
        if(stripos($path, $base) === 0):
            return substr($path, strlen($base));
        endif;

        $best = null;
        foreach(['vendor/', 'app/', 'resources/', 'routes/', 'storage/', 'database/', 'config/', 'bootstrap/', 'tests/', 'lang/', 'public_html/', 'public/'] as $root):
            $pos = strpos($path, '/'.$root);
            if($pos !== false && ($best === null || $pos < $best)):
                $best = $pos;
            endif;
        endforeach;

        return ($best !== null ? substr($path, $best + 1) : $path);
    }

    /**
     * app = our code, view = a compiled Blade view, vendor = framework/packages.
     */
    public static function kind(string $relativePath): string
    {
        if(strpos($relativePath, 'vendor/') === 0):
            return 'vendor';
        endif;
        if(strpos($relativePath, 'storage/framework/views/') === 0):
            return 'view';
        endif;
        foreach(['app/', 'routes/', 'database/', 'config/', 'resources/'] as $root):
            if(strpos($relativePath, $root) === 0):
                return 'app';
            endif;
        endforeach;

        return 'other';
    }

    /**
     * The .blade.php a compiled view was built from, read from the footer the
     * Blade compiler writes, when that compiled file exists on this server.
     */
    public static function compiledViewSource(string $relativePath): ?string
    {
        $name = basename($relativePath);
        if(!preg_match('/^[a-f0-9]{20,64}\.php$/', $name)):
            return null;
        endif;
        if(array_key_exists($name, self::$viewSources)):
            return self::$viewSources[$name];
        endif;

        $source = null;
        $path = storage_path('framework/views/'.$name);
        if(is_file($path) && ($handle = @fopen($path, 'rb'))):
            fseek($handle, max(0, filesize($path) - 1024));
            $tail = (string) fread($handle, 1024);
            fclose($handle);
            if(preg_match('/\/\*\*PATH (.+?) ENDPATH\*\*\//', $tail, $m)):
                $source = self::relativePath($m[1]);
            endif;
        endif;

        return self::$viewSources[$name] = $source;
    }

    public static function unescape(string $text): string
    {
        return strtr($text, ['\\\\' => '\\', '\\"' => '"', '\\/' => '/']);
    }

    /**
     * Drops the "} that closes the context JSON (and an empty "[]" extra).
     */
    private static function stripContextClose(string $text): string
    {
        return preg_replace('/"\}\s*(?:\[\]\s*)?$/', '', rtrim($text));
    }

    private static function shortenPaths(string $text): string
    {
        $base = rtrim(base_path(), '\\/');

        return str_ireplace([$base.DIRECTORY_SEPARATOR, str_replace('\\', '/', $base).'/', $base.'/'], '', $text);
    }

    public static function cut(string $text, int $bytes): string
    {
        if(strlen($text) <= $bytes):
            return $text;
        endif;

        return rtrim(mb_strcut($text, 0, $bytes, 'UTF-8')).'…';
    }

    public static function bytes($bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while($bytes >= 1024 && $i < count($units) - 1):
            $bytes /= 1024;
            $i++;
        endwhile;

        return ($i === 0 ? $bytes.' B' : number_format($bytes, 1).' '.$units[$i]);
    }

    public static function levelRank(string $level): int
    {
        return (isset(self::LEVELS[$level]) ? self::LEVELS[$level] : 0);
    }
}
