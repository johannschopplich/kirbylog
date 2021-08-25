<?php

if (!function_exists('kirbylog')) {
    /**
     * Logs content to file
     *
     * @param string|int|array $content Can be a string, integer or an array. Arrays will be converted to JSON.
     * @param string|null $level Case-insensitive logging level.
     * @return void
     */
    function kirbylog($content, ?string $level = null): void
    {
        $logLevels = option('johannschopplich.kirbylog.levels', [
            'DEBUG',
            'INFO',
            'NOTICE',
            'WARNING',
            'ERROR',
            'CRITICAL',
            'ALERT',
            'EMERGENCY'
        ]);

        $level = strtoupper($level ?? option('johannschopplich.kirbylog.defaultLevel', 'info'));
        if (
            option('debug') &&
            !in_array($level, $logLevels)
        ) {
            throw new \UnexpectedValueException("Level \"{$level}\" is not part of the logging levels described");
        }

        if (is_array($content) || is_object($content)) {
            $content = json_encode($content, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }

        $dir = option('johannschopplich.kirbylog.dir', kirby()->root('logs'));
        $filename = option('johannschopplich.kirbylog.filename', strftime('%Y-%m-%d') . '.log');

        if (is_callable($dir)) {
            $dir = $dir();
        }

        \Kirby\Toolkit\F::append(
            $dir . '/' . $filename,
            '[' . strftime('%Y-%m-%d %H:%M:%S') . '] ' . $level . ' ' . $content . "\n"
        );
    }
}
