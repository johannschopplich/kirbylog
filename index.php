<?php

use Kirby\Cms\App;
use Kirby\Filesystem\F;

App::plugin('johannschopplich/kirbylog', []);

if (!function_exists('kirbylog')) {
    /**
     * Logs content to file.
     */
    function kirbylog(mixed $content, string|null $level = null): void
    {
        $kirby = App::instance();
        $level = strtoupper($level ?? $kirby->option('johannschopplich.kirbylog.defaultLevel') ?? 'info');
        $logLevels = array_map('strtoupper', $kirby->option('johannschopplich.kirbylog.levels') ?? [
            // RFC 5424
            'DEBUG',
            'INFO',
            'NOTICE',
            'WARNING',
            'ERROR',
            'CRITICAL',
            'ALERT',
            'EMERGENCY'
        ]);

        if (
            $kirby->option('debug') &&
            !in_array($level, $logLevels, true)
        ) {
            throw new \UnexpectedValueException("Level \"{$level}\" is not part of the logging levels described");
        }

        $content = match (true) {
            is_string($content) => $content,
            $content instanceof Stringable => (string)$content,
            default => json_encode($content, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_INVALID_UTF8_SUBSTITUTE) ?: rtrim(print_r($content, true), "\n")
        };

        $dir = $kirby->option('johannschopplich.kirbylog.dir') ?? $kirby->root('logs');
        $filename = $kirby->option('johannschopplich.kirbylog.filename') ?? date('Y-m-d') . '.log';

        if (is_callable($dir)) {
            $dir = $dir();
        }

        F::append(
            $dir . '/' . $filename,
            '[' . date('Y-m-d H:i:s') . '] ' . $level . ' ' . $content . "\n"
        );
    }
}
