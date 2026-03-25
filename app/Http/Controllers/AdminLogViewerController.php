<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class AdminLogViewerController extends Controller
{
    private const MAX_DISPLAY_BYTES = 524288; // 512 KB

    /** @var list<string> */
    private const FILTER_OPTIONS = ['all', 'errors', 'warning', 'notice', 'info', 'debug'];

    /**
     * Resolve and validate a log file path under storage/logs. Returns full path or null.
     */
    private function resolveLogPath(string $name): ?string
    {
        $name = basename($name);
        if ($name === '' || $name === '.' || $name === '..' || ! Str::endsWith(strtolower($name), '.log')) {
            return null;
        }
        $fullPath = storage_path('logs/'.$name);
        $realLogs = realpath(storage_path('logs'));
        $realFile = realpath($fullPath);
        if ($realFile === false || $realLogs === false || ! str_starts_with($realFile, $realLogs) || ! File::isFile($fullPath)) {
            return null;
        }

        return $fullPath;
    }

    /**
     * List log files in storage/logs (admin only via routes).
     */
    public function index()
    {
        $logPath = storage_path('logs');
        $files = collect();

        if (File::isDirectory($logPath)) {
            $files = collect(File::files($logPath))
                ->filter(function (\SplFileInfo $f) {
                    return Str::endsWith(strtolower($f->getFilename()), '.log');
                })
                ->sortByDesc(fn (\SplFileInfo $f) => $f->getMTime())
                ->values()
                ->map(function (\SplFileInfo $f) {
                    return [
                        'name' => $f->getFilename(),
                        'size' => $f->getSize(),
                        'modified' => $f->getMTime(),
                    ];
                });
        }

        return view('admin.logs.index', compact('files'));
    }

    /**
     * View a single log file (basename only, .log only, under storage/logs).
     *
     * Query: filter=all|errors|warning|notice|info|debug (Laravel channel.level lines)
     */
    public function show(Request $request)
    {
        $name = basename((string) $request->query('file', ''));
        $fullPath = $this->resolveLogPath($name);
        if ($fullPath === null) {
            abort(404);
        }

        $filter = strtolower((string) $request->query('filter', 'all'));
        if (! in_array($filter, self::FILTER_OPTIONS, true)) {
            $filter = 'all';
        }

        $size = File::size($fullPath);
        $truncated = false;
        $content = '';

        if ($size <= self::MAX_DISPLAY_BYTES) {
            $content = File::get($fullPath);
        } else {
            $truncated = true;
            $handle = fopen($fullPath, 'rb');
            if ($handle === false) {
                abort(500, 'Could not read log file.');
            }
            fseek($handle, max(0, $size - self::MAX_DISPLAY_BYTES));
            $content = fread($handle, self::MAX_DISPLAY_BYTES);
            fclose($handle);
            $content = '… Log file is large ('.$this->formatBytes($size).'). Showing last '.$this->formatBytes(self::MAX_DISPLAY_BYTES)." only.\n\n".$content;
        }

        [$metaPrefix, $body] = $this->splitMetaPrefix($content);
        $lines = $this->classifyLogLines($body);
        if ($filter !== 'all') {
            $lines = $this->filterLinesByIdentity($lines, $filter);
        }

        $counts = $this->countLevels($this->classifyLogLines($body));

        return view('admin.logs.show', [
            'fileName' => $name,
            'metaPrefix' => $metaPrefix,
            'lines' => $lines,
            'truncated' => $truncated,
            'fileSize' => $size,
            'filter' => $filter,
            'filterOptions' => self::FILTER_OPTIONS,
            'levelCounts' => $counts,
        ]);
    }

    /**
     * Empty a log file (admin only).
     */
    public function clear(Request $request)
    {
        $request->validate([
            'file' => ['required', 'string', 'max:255'],
        ]);

        $fullPath = $this->resolveLogPath($request->input('file'));
        if ($fullPath === null) {
            return redirect()->route('admin.logs')->with('error', 'Invalid log file.');
        }

        File::put($fullPath, '');

        return redirect()
            ->route('admin.logs.show', ['file' => basename($request->input('file'))])
            ->with('success', 'Log file cleared: '.basename($request->input('file')));
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function splitMetaPrefix(string $content): array
    {
        if (str_starts_with($content, '…')) {
            $pos = strpos($content, "\n\n");
            if ($pos !== false) {
                return [
                    substr($content, 0, $pos + 2),
                    substr($content, $pos + 2),
                ];
            }
        }

        return ['', $content];
    }

    /**
     * @return list<array{text: string, level: string}>
     */
    private function classifyLogLines(string $body): array
    {
        $raw = preg_split('/\r\n|\r|\n/', $body);
        $out = [];
        foreach ($raw as $line) {
            $level = 'plain';
            if (preg_match('/\.(EMERGENCY|ALERT|CRITICAL|ERROR):/i', $line)) {
                $level = 'error';
            } elseif (preg_match('/\.WARNING:/i', $line)) {
                $level = 'warning';
            } elseif (preg_match('/\.NOTICE:/i', $line)) {
                $level = 'notice';
            } elseif (preg_match('/\.INFO:/i', $line)) {
                $level = 'info';
            } elseif (preg_match('/\.DEBUG:/i', $line)) {
                $level = 'debug';
            }
            $out[] = ['text' => $line, 'level' => $level];
        }

        return $out;
    }

    /**
     * @param  list<array{text: string, level: string}>  $lines
     * @return list<array{text: string, level: string}>
     */
    private function filterLinesByIdentity(array $lines, string $filter): array
    {
        if ($filter === 'errors') {
            return array_values(array_filter($lines, fn (array $l) => $l['level'] === 'error'));
        }

        return array_values(array_filter($lines, fn (array $l) => $l['level'] === $filter));
    }

    /**
     * @param  list<array{text: string, level: string}>  $lines
     * @return array<string, int>
     */
    private function countLevels(array $lines): array
    {
        $c = ['error' => 0, 'warning' => 0, 'notice' => 0, 'info' => 0, 'debug' => 0, 'plain' => 0];
        foreach ($lines as $l) {
            $lev = $l['level'];
            if (isset($c[$lev])) {
                $c[$lev]++;
            }
        }

        return $c;
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2).' '.$units[$i];
    }
}
