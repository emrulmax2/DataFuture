<?php

namespace App\Http\Controllers;

use App\Services\ErrorLogService;
use Illuminate\Http\Request;

/**
 * Site Settings > Error Logs: a developer view over storage/logs. Every action
 * checks the "Error Logs" privilege itself as well as the route map, because
 * log entries carry stack traces, SQL and personal data.
 */
class ErrorLogController extends Controller
{
    public function index(ErrorLogService $logs)
    {
        $this->authorizeView();

        $files = $logs->files();

        return view('pages.settings.error-logs.index', [
            'title' => 'Error Logs - London Churchill College',
            'subtitle' => 'Error Logs',
            'slug' => 'error_logs',
            'breadcrumbs' => [
                ['label' => 'Settings', 'href' => 'javascript:void(0);'],
                ['label' => 'Error Logs', 'href' => 'javascript:void(0);']
            ],
            'files' => $files,
        ]);
    }

    public function sync(Request $request, ErrorLogService $logs)
    {
        $this->authorizeView();

        $status = $logs->sync((string) $request->input('file'));
        if($status === null):
            return response()->json(['message' => 'That log file no longer exists.'], 404);
        endif;

        $file = collect($logs->files())->firstWhere('name', $request->input('file'));

        return response()->json(array_merge($status, ['file' => $file]));
    }

    public function list(Request $request, ErrorLogService $logs)
    {
        $this->authorizeView();

        $file = (string) $request->input('file');
        if($logs->resolve($file) === null):
            return response()->json(['last_page' => 1, 'total_rows' => 0, 'data' => [], 'facets' => ['levels' => [], 'envs' => [], 'matched' => 0, 'total' => 0]]);
        endif;

        $levels = (isset($request->levels) && !empty($request->levels) ? explode(',', $request->levels) : []);
        $sorters = (isset($request->sorters) && is_array($request->sorters) ? $request->sorters : []);
        $mode = ($request->input('mode') === 'entries' ? 'entries' : 'groups');
        $page = (isset($request->page) && $request->page > 0 ? (int) $request->page : 1);
        $size = (isset($request->size) && $request->size > 0 ? (int) $request->size : 25);

        $result = $logs->query($file, [
            'levels' => $levels,
            'env' => (string) $request->input('env'),
            'q' => (string) $request->input('q'),
            'from' => $this->date($request->input('from')),
            'to' => $this->date($request->input('to')),
            'fp' => (string) $request->input('fp'),
            'user' => $request->input('user'),
        ], $mode, $sorters, $page, $size);

        return response()->json($result, 200, [], JSON_INVALID_UTF8_SUBSTITUTE);
    }

    public function show(Request $request, ErrorLogService $logs)
    {
        $this->authorizeView();

        $file = (string) $request->input('file');
        $entry = $logs->entry($file, (int) $request->input('offset'));
        if($entry === null):
            return response()->json(['message' => 'This entry is no longer in the log. Refresh the list and try again.'], 404);
        endif;

        $entry['download_url'] = route('error.logs.entry.download', ['file' => $file, 'offset' => $entry['offset']]);

        return response()->json($entry, 200, [], JSON_INVALID_UTF8_SUBSTITUTE);
    }

    public function downloadEntry(Request $request, ErrorLogService $logs)
    {
        $this->authorizeView();

        $file = (string) $request->input('file');
        $entry = $logs->entryBytes($file, (int) $request->input('offset'));
        if($entry === null):
            abort(404);
        endif;

        $name = pathinfo($file, PATHINFO_FILENAME).'-'.date('Y-m-d-His', $entry['timestamp']).'-'.strtolower($entry['level']).'.log';

        return response($entry['text'], 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$name.'"',
        ]);
    }

    public function download(Request $request, ErrorLogService $logs)
    {
        $this->authorizeView();

        $path = $logs->resolve((string) $request->input('file'));
        if($path === null):
            abort(404);
        endif;

        return response()->download($path, basename($path), ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    private function authorizeView()
    {
        if(!ErrorLogService::canView()):
            abort(403);
        endif;
    }

    private function date($value)
    {
        return (is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) ? $value : null);
    }
}
