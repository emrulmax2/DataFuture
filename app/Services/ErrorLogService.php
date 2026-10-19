<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;

/**
 * Developer view over the files in storage/logs.
 *
 * A log file can run to gigabytes, so it is never parsed per request. sync()
 * scans it once into a compact index (one small row per entry, with the byte
 * offset of the entry) and afterwards only scans what has been appended since.
 * Listing, filtering and grouping run on that index; the full entry is read
 * from the file by offset only when one is opened.
 *
 * Index files live in storage/framework/cache/error-log-index and can be
 * deleted at any time; they are rebuilt on the next visit.
 */
class ErrorLogService
{
    /** The priv() key. See LegacyPrivilegeMap: settings.error_logs. */
    const PRIVILEGE = 'error_logs';

    /**
     * Bump whenever ErrorLogParser::summary() changes what it extracts: an
     * unchanged log file is never re-scanned, so its old index rows would stay.
     */
    const INDEX_VERSION = 1;

    /** Seconds of scanning per sync() call; the page keeps calling until done. */
    public static $timeBudget = 5.0;

    /** Most of one entry read for the detail view. */
    const ENTRY_MAX_BYTES = 16777216;

    /** Most of one entry shown on the Raw tab. */
    const RAW_MAX_BYTES = 262144;

    const FILE_PATTERN = '/^[A-Za-z0-9][A-Za-z0-9._-]*\.log$/';

    public static function canView(): bool
    {
        $user = auth()->user();
        if(!$user):
            return false;
        endif;

        $priv = $user->priv();

        return isset($priv[self::PRIVILEGE]) && $priv[self::PRIVILEGE] == 1;
    }

    /**
     * The .log files in storage/logs, laravel.log first, then newest first.
     */
    public function files(): array
    {
        $files = [];
        foreach((array) glob(storage_path('logs/*.log')) as $path):
            $name = basename($path);
            if(!preg_match(self::FILE_PATTERN, $name) || !is_file($path)):
                continue;
            endif;

            $files[] = [
                'name' => $name,
                'size' => filesize($path),
                'size_human' => ErrorLogParser::bytes(filesize($path)),
                'modified' => filemtime($path),
                'modified_human' => date('d M Y, H:i', filemtime($path)),
            ];
        endforeach;

        usort($files, function($a, $b){
            if($a['name'] === 'laravel.log' || $b['name'] === 'laravel.log'):
                return ($a['name'] === 'laravel.log' ? -1 : 1);
            endif;

            return $b['modified'] <=> $a['modified'];
        });

        return $files;
    }

    /**
     * Full path of a log file by name, or null. Only names that are listed in
     * storage/logs resolve, so a request can never point outside it.
     */
    public function resolve($name): ?string
    {
        if(!is_string($name) || !preg_match(self::FILE_PATTERN, $name)):
            return null;
        endif;

        $path = storage_path('logs/'.$name);

        return (is_file($path) ? $path : null);
    }

    /**
     * Brings the index of $name up to date, scanning for at most $timeBudget
     * seconds. Returns where it got to; call again while 'done' is false.
     */
    public function sync(string $name): ?array
    {
        $path = $this->resolve($name);
        if(!$path):
            return null;
        endif;

        $indexPath = $this->indexPath($name);
        if(!is_dir(dirname($indexPath))):
            @mkdir(dirname($indexPath), 0775, true);
        endif;

        $lock = fopen($indexPath.'.lock', 'c');
        if(!$lock || !flock($lock, LOCK_EX | LOCK_NB)):
            return ['busy' => true, 'done' => false, 'progress' => 0, 'entries' => 0];
        endif;

        try {
            @set_time_limit(120);
            clearstatcache(true, $path);
            $size = filesize($path);
            $mtime = filemtime($path);
            $signature = $this->headSignature($path);

            $index = $this->loadIndex($name);
            if(!$index || $index['signature'] !== $signature || $size < $index['scanned']):
                $index = ['v' => self::INDEX_VERSION, 'file' => $name, 'signature' => $signature, 'size' => 0, 'mtime' => 0, 'scanned' => 0, 'resume' => 0, 'done' => false, 'entries' => []];
            endif;

            if($index['done'] && $index['size'] === $size && $index['mtime'] === $mtime):
                return $this->syncStatus($index, $size);
            endif;

            $index = $this->scan($path, $index);
            $index['size'] = $size;
            $index['mtime'] = $mtime;
            $this->saveIndex($indexPath, $index);

            return $this->syncStatus($index, $size);
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    /**
     * Reads from the resume point, appending one index row per entry. The last
     * entry is re-read on the next call (it may still have been growing), so
     * the resume point is always the start of an entry.
     */
    private function scan(string $path, array $index): array
    {
        $resume = (int) $index['resume'];
        while(!empty($index['entries']) && end($index['entries'])[0] >= $resume):
            array_pop($index['entries']);
        endwhile;

        $handle = fopen($path, 'rb');
        fseek($handle, $resume);
        $started = microtime(true);
        $current = null;
        $cutAt = null;

        while(true):
            $offset = ftell($handle);
            $line = fgets($handle);
            if($line === false):
                break;
            endif;

            if($line[0] === '[' && ErrorLogParser::header($line) !== null):
                if($current !== null):
                    $this->finish($index, $current, $offset);
                    if(microtime(true) - $started > self::$timeBudget):
                        $cutAt = $offset;
                        $current = null;
                        break;
                    endif;
                endif;
                $current = ['offset' => $offset, 'line' => rtrim($line, "\r\n"), 'exception' => '', 'onFirst' => false, 'stage' => 0, 'frames' => 0, 'user' => null];
                $this->feed($current, $line, true);
            elseif($current !== null):
                $this->feed($current, $line, false);
            endif;
        endwhile;

        $end = ftell($handle);
        fclose($handle);

        if($cutAt !== null):
            $index['resume'] = $cutAt;
            $index['scanned'] = $cutAt;
            $index['done'] = false;
        else:
            if($current !== null):
                $this->finish($index, $current, $end);
                $index['resume'] = $current['offset'];
            else:
                $index['resume'] = $end;
            endif;
            $index['scanned'] = $end;
            $index['done'] = true;
        endif;

        return $index;
    }

    /**
     * Collects, per entry, only what the index row needs: the exception
     * header (wherever in the entry it starts) and stack frames up to the
     * first one in our own code.
     */
    private function feed(array &$entry, string $line, bool $isFirst)
    {
        if($entry['stage'] === 0):
            $marker = strpos($line, ErrorLogParser::EXCEPTION_MARKER);
            if($marker !== false):
                $entry['exception'] = rtrim(substr($line, $marker + strlen('"exception":"')), "\r\n");
                $entry['onFirst'] = $isFirst;
                $entry['stage'] = 1;
                if(preg_match('/"userId":(\d+)/', substr($line, 0, $marker), $m)):
                    $entry['user'] = (int) $m[1];
                endif;
            elseif($entry['user'] === null && strpos($line, '"userId":') !== false && preg_match('/"userId":(\d+)/', $line, $m)):
                $entry['user'] = (int) $m[1];
            endif;

            return;
        endif;

        if($entry['stage'] === 1):
            if(strncmp($line, '[stacktrace]', 12) === 0):
                $entry['exception'] .= "\n[stacktrace]";
                $entry['stage'] = 2;
            elseif(strlen($entry['exception']) < 16384):
                $entry['exception'] .= "\n".rtrim($line, "\r\n");
            endif;

            return;
        endif;

        if($entry['stage'] === 2):
            if($line[0] !== '#' || $entry['frames'] >= 80):
                $entry['stage'] = 3;

                return;
            endif;

            $entry['exception'] .= "\n".rtrim($line, "\r\n");
            $entry['frames']++;
            if(strpos($line, 'vendor') === false && preg_match('#[\\\\/](app|routes|resources|database)[\\\\/]#', $line)):
                $entry['stage'] = 3;
            endif;
        endif;
    }

    private function finish(array &$index, array $entry, int $end)
    {
        $row = ErrorLogParser::summary($entry['line'], $entry['exception'], $entry['onFirst'], $entry['user']);
        if($row !== null):
            array_unshift($row, $entry['offset'], $end - $entry['offset']);
            $index['entries'][] = $row;
        endif;
    }

    private function syncStatus(array $index, int $size): array
    {
        return [
            'busy' => false,
            'done' => (bool) $index['done'],
            'progress' => ($size > 0 ? min(100, (int) floor($index['scanned'] / $size * 100)) : 100),
            'entries' => count($index['entries']),
        ];
    }

    /**
     * The table rows for one page, plus level/environment counts for the chips.
     *
     * $filters: levels (array), env, q, from, to (Y-m-d), fp, user.
     * $mode: 'groups' (one row per fingerprint) or 'entries'.
     */
    public function query(string $name, array $filters, string $mode, array $sorters, int $page, int $size): array
    {
        $index = $this->loadIndex($name);
        $entries = ($index ? $index['entries'] : []);

        $levels = array_filter(array_map('strtoupper', (array) ($filters['levels'] ?? [])));
        $env = (string) ($filters['env'] ?? '');
        $search = trim((string) ($filters['q'] ?? ''));
        $fingerprint = (string) ($filters['fp'] ?? '');
        $user = (isset($filters['user']) && $filters['user'] !== '' ? (int) $filters['user'] : null);
        $from = (!empty($filters['from']) ? strtotime($filters['from'].' 00:00:00') : null);
        $to = (!empty($filters['to']) ? strtotime($filters['to'].' 23:59:59') : null);

        $groupSize = [];
        $envCounts = [];
        foreach($entries as $e):
            $groupSize[$e[9]] = ($groupSize[$e[9]] ?? 0) + 1;
            $envCounts[$e[3]] = ($envCounts[$e[3]] ?? 0) + 1;
        endforeach;

        $levelCounts = [];
        $matched = [];
        foreach($entries as $e):
            if(($from && $e[2] < $from) || ($to && $e[2] > $to)):
                continue;
            endif;
            if($env !== '' && $e[3] !== $env):
                continue;
            endif;
            if($fingerprint !== '' && $e[9] !== $fingerprint):
                continue;
            endif;
            if($user !== null && $e[8] !== $user):
                continue;
            endif;
            if($search !== '' && stripos($e[5].' '.$e[6].' '.$e[7], $search) === false):
                continue;
            endif;

            $levelCounts[$e[4]] = ($levelCounts[$e[4]] ?? 0) + 1;
            if(!empty($levels) && !in_array($e[4], $levels, true)):
                continue;
            endif;

            $matched[] = $e;
        endforeach;

        $sort = (isset($sorters[0]['field']) ? (string) $sorters[0]['field'] : '');
        $desc = !(isset($sorters[0]['dir']) && $sorters[0]['dir'] === 'asc');

        if($mode === 'groups'):
            $rows = $this->groups($matched, $sort, $desc);
        else:
            $rows = $this->sortEntries($matched, $sort, $desc);
        endif;

        $total = count($rows);
        $size = max(1, min($size, 500));
        $lastPage = max(1, (int) ceil($total / $size));
        $page = max(1, min($page, $lastPage));
        $slice = array_slice($rows, ($page - 1) * $size, $size);

        $data = ($mode === 'groups' ? $this->presentGroups($slice) : $this->presentEntries($slice, $groupSize));

        uksort($levelCounts, function($a, $b){
            return ErrorLogParser::levelRank($b) <=> ErrorLogParser::levelRank($a);
        });
        arsort($envCounts);

        return [
            'last_page' => $lastPage,
            'total_rows' => $total,
            'data' => $data,
            'facets' => [
                'levels' => $levelCounts,
                'envs' => $envCounts,
                'matched' => count($matched),
                'total' => count($entries),
            ],
        ];
    }

    private function sortEntries(array $entries, string $field, bool $desc): array
    {
        usort($entries, function($a, $b) use($field, $desc){
            if($field === 'level'):
                $cmp = (ErrorLogParser::levelRank($a[4]) <=> ErrorLogParser::levelRank($b[4])) ?: ($a[0] <=> $b[0]);
            elseif($field === 'env'):
                $cmp = strcmp($a[3], $b[3]) ?: ($a[0] <=> $b[0]);
            else:
                $cmp = ($a[2] <=> $b[2]) ?: ($a[0] <=> $b[0]);
            endif;

            return ($desc ? -$cmp : $cmp);
        });

        return $entries;
    }

    private function groups(array $entries, string $field, bool $desc): array
    {
        $groups = [];
        foreach($entries as $e):
            $fp = $e[9];
            if(!isset($groups[$fp])):
                $groups[$fp] = ['fp' => $fp, 'count' => 0, 'first' => $e[2], 'last' => $e[2], 'latest' => $e, 'rank' => 0, 'envs' => []];
            endif;

            $group = &$groups[$fp];
            $group['count']++;
            $group['first'] = min($group['first'], $e[2]);
            if($e[2] >= $group['last']):
                $group['last'] = $e[2];
                $group['latest'] = $e;
            endif;
            $group['rank'] = max($group['rank'], ErrorLogParser::levelRank($e[4]));
            $group['envs'][$e[3]] = true;
            unset($group);
        endforeach;

        $groups = array_values($groups);
        usort($groups, function($a, $b) use($field, $desc){
            if($field === 'count'):
                $cmp = ($a['count'] <=> $b['count']) ?: ($a['last'] <=> $b['last']);
            elseif($field === 'first_seen'):
                $cmp = $a['first'] <=> $b['first'];
            elseif($field === 'level'):
                $cmp = ($a['rank'] <=> $b['rank']) ?: ($a['count'] <=> $b['count']);
            else:
                $cmp = ($a['last'] <=> $b['last']) ?: ($a['count'] <=> $b['count']);
            endif;

            return ($desc ? -$cmp : $cmp);
        });

        return $groups;
    }

    private function presentEntries(array $entries, array $groupSize): array
    {
        $users = $this->userNames(array_column($entries, 8));
        $data = [];
        foreach($entries as $e):
            $data[] = [
                'offset' => $e[0],
                'time' => date('d M Y H:i:s', $e[2]),
                'ago' => Carbon::createFromTimestamp($e[2])->diffForHumans(),
                'env' => $e[3],
                'level' => $e[4],
                'message' => $e[5],
                'class' => $e[6],
                'where' => $e[7],
                'user_id' => $e[8],
                'user_name' => ($e[8] !== null && isset($users[$e[8]]) ? $users[$e[8]] : ''),
                'fp' => $e[9],
                'occurrences' => ($groupSize[$e[9]] ?? 1),
            ];
        endforeach;

        return $data;
    }

    private function presentGroups(array $groups): array
    {
        $data = [];
        foreach($groups as $group):
            $e = $group['latest'];
            $data[] = [
                'fp' => $group['fp'],
                'offset' => $e[0],
                'count' => $group['count'],
                'level' => $e[4],
                'message' => $e[5],
                'class' => $e[6],
                'where' => $e[7],
                'envs' => implode(', ', array_keys($group['envs'])),
                'first_seen' => date('d M Y H:i', $group['first']),
                'last_seen' => date('d M Y H:i', $group['last']),
                'last_seen_ago' => Carbon::createFromTimestamp($group['last'])->diffForHumans(),
            ];
        endforeach;

        return $data;
    }

    /**
     * One entry, parsed for the detail view. Null when the offset is not an
     * entry of the current index (the file changed since the list was drawn).
     */
    public function entry(string $name, int $offset): ?array
    {
        $index = $this->loadIndex($name);
        $row = ($index ? $this->findRow($index['entries'], $offset) : null);
        $text = ($row ? $this->readEntry($name, $row) : null);
        if($text === null):
            return null;
        endif;

        $truncated = $row[1] > strlen($text);
        $detail = ErrorLogParser::detail($text, $truncated);
        if(!$detail):
            return null;
        endif;

        $count = 0;
        $first = null;
        $last = null;
        foreach($index['entries'] as $e):
            if($e[9] === $row[9]):
                $count++;
                $first = ($first === null ? $e[2] : min($first, $e[2]));
                $last = ($last === null ? $e[2] : max($last, $e[2]));
            endif;
        endforeach;

        $users = $this->userNames([$row[8]]);
        $raw = ErrorLogParser::cut($text, self::RAW_MAX_BYTES);

        return array_merge($detail, [
            'offset' => $row[0],
            'time' => date('d M Y H:i:s', $row[2]),
            'ago' => Carbon::createFromTimestamp($row[2])->diffForHumans(),
            'class' => $row[6],
            'where' => $row[7],
            'user_id' => $row[8],
            'user_name' => ($row[8] !== null && isset($users[$row[8]]) ? $users[$row[8]] : ''),
            'fp' => $row[9],
            'group' => [
                'count' => $count,
                'first_seen' => ($first ? date('d M Y H:i', $first) : ''),
                'last_seen' => ($last ? date('d M Y H:i', $last) : ''),
            ],
            'size' => $row[1],
            'size_human' => ErrorLogParser::bytes($row[1]),
            'raw' => $raw,
            'raw_truncated' => strlen($raw) < $row[1],
        ]);
    }

    /**
     * The exact bytes of one entry, for downloading it on its own.
     */
    public function entryBytes(string $name, int $offset): ?array
    {
        $index = $this->loadIndex($name);
        $row = ($index ? $this->findRow($index['entries'], $offset) : null);
        if(!$row):
            return null;
        endif;

        $text = $this->readEntry($name, $row);

        return ($text === null ? null : ['text' => $text, 'timestamp' => $row[2], 'level' => $row[4]]);
    }

    private function readEntry(string $name, array $row): ?string
    {
        $path = $this->resolve($name);
        if(!$path || $row[0] + $row[1] > filesize($path)):
            return null;
        endif;

        $handle = fopen($path, 'rb');
        fseek($handle, $row[0]);
        $text = (string) stream_get_contents($handle, min($row[1], self::ENTRY_MAX_BYTES));
        fclose($handle);

        return (ErrorLogParser::header(strtok($text, "\n")) !== null ? $text : null);
    }

    /** Index rows are in file order, so the offset is found by binary search. */
    private function findRow(array $entries, int $offset): ?array
    {
        $low = 0;
        $high = count($entries) - 1;
        while($low <= $high):
            $mid = ($low + $high) >> 1;
            if($entries[$mid][0] === $offset):
                return $entries[$mid];
            endif;
            if($entries[$mid][0] < $offset):
                $low = $mid + 1;
            else:
                $high = $mid - 1;
            endif;
        endwhile;

        return null;
    }

    private function userNames(array $ids): array
    {
        $ids = array_values(array_unique(array_filter($ids)));

        return (empty($ids) ? [] : User::whereIn('id', $ids)->pluck('name', 'id')->all());
    }

    private function loadIndex(string $name): ?array
    {
        $path = $this->indexPath($name);
        if(!is_file($path)):
            return null;
        endif;

        $index = json_decode((string) file_get_contents($path), true);
        if(!is_array($index) || ($index['v'] ?? null) !== self::INDEX_VERSION || ($index['file'] ?? null) !== $name):
            return null;
        endif;

        return $index;
    }

    private function saveIndex(string $path, array $index)
    {
        $temp = $path.'.'.getmypid().'.tmp';
        file_put_contents($temp, json_encode($index, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE));
        if(!@rename($temp, $path)):
            @unlink($path);
            @rename($temp, $path);
        endif;
    }

    private function indexPath(string $name): string
    {
        return storage_path('framework/cache/error-log-index/'.md5($name).'.json');
    }

    /** Changes when the file is rotated or cleared, which forces a rebuild. */
    private function headSignature(string $path): string
    {
        $handle = fopen($path, 'rb');
        $head = (string) fread($handle, 512);
        fclose($handle);

        return md5($head);
    }
}
