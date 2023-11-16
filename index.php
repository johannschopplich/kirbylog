<?php

use \Kirby\Cms\App;

App::plugin('johannschopplich/kirbylog', []);

if (!function_exists('kirbylog')) {
    /**
     * Logs content to file
     */
    function kirbylog(string|int|array $content, string|null $level = null): void
    {
        $kirby = App::instance();
        $level = strtoupper($level ?? $kirby->option('johannschopplich.kirbylog.defaultLevel', 'info'));
        $logLevels = $kirby->option('johannschopplich.kirbylog.levels', [
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
            !in_array($level, $logLevels)
        ) {
            throw new \UnexpectedValueException("Level \"{$level}\" is not part of the logging levels described");
        }

        if (is_array($content) || is_object($content)) {
            $content = json_encode($content, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }

        $dir = $kirby->option('johannschopplich.kirbylog.dir', $kirby->root('logs'));
        $filename = $kirby->option('johannschopplich.kirbylog.filename', date('Y-m-d') . '.log');

        if (is_callable($dir)) {
            $dir = $dir();
        }

        \Kirby\Filesystem\F::append(
            $dir . '/' . $filename,
            '[' . date('Y-m-d H:i:s') . '] ' . $level . ' ' . $content . "\n"
        );
    }
}
