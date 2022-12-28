<?php

\Kirby\Cms\App::plugin('johannschopplich/kirbylog', []);

if (!function_exists('kirbylog')) {
    /**
     * Logs content to file
     *
     * @param string|int|array $content Can be a string, integer or an array. Arrays will be converted to JSON.
     * @param string|null $level Case-insensitive logging level.
     * @return void
     */
    function kirbylog($content, string|null $level = null): void
    {
        $kirby = kirby();

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
